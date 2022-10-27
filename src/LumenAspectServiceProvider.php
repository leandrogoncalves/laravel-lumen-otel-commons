<?php
declare(strict_types=1);

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 *
 * Copyright (c) 2015-2020 Yuuki Takezawa
 *
 */

namespace Picpay\LaravelAspect;

use Jaeger\Config;
use OpenTracing\GlobalTracer;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\ServiceProvider;

/**
 * Class LumenAspectServiceProvider
 *
 * @codeCoverageIgnore
 */
class LumenAspectServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->configure('picpay-laravel-aop');
        $this->app->singleton('aspect.manager', function ($app) {
            /** @var AnnotationConfiguration $annotationConfiguration */
            $annotationConfiguration = $app->make(AnnotationConfiguration::class);
            $annotationConfiguration->ignoredAnnotations();
            return new AspectManager($app);
        });

        $this->app->singleton(AnnotationConfiguration::class, function ($app) {
            $annotationConfiguration = new AnnotationConfiguration(
                $app['config']->get('picpay-laravel-aop.annotation')
            );

            return $annotationConfiguration;
        });
    }

    public function boot(): void
    {
        $this->app['aspect.manager']->weave();

        $this->app->singleton(Jaeger::class, static function () {
            $config = new Config(
                config('picpay-laravel-aop.tracing.config'),
                config('picpay-laravel-aop.tracing.service_name'),
            );

            $config->initializeTracer();

            $client = GlobalTracer::get();

            return new Jaeger($client);
        });      

        $this->initHttp();
        $this->initConsole();
        $this->initQuery();
        $this->initJob();
        
        app(Jaeger::class)->finish();
    }

    private function initHttp(): void
    {
        if (config('picpay-laravel-aop.tracing.listeners.http.enabled') && false === $this->app->runningInConsole()) {

            $this->app->middleware([
                    config('picpay-laravel-aop.tracing.listeners.http.handler')
            ]);
        }
    }

    private function initConsole(): void
    {
        if (config('picpay-laravel-aop.tracing.listeners.console.enabled') && $this->app->runningInConsole()) {
            Event::listen(CommandStarting::class, config('picpay-laravel-aop.tracing.listeners.console.handler'));
            Event::listen(CommandFinished::class, config('picpay-laravel-aop.tracing.listeners.console.handler'));
        }
    }

    private function initQuery(): void
    {
        if (config('picpay-laravel-aop.tracing.listeners.query.enabled')) {
            Event::listen(QueryExecuted::class, config('picpay-laravel-aop.tracing.listeners.query.handler'));
        }
    }

    private function initJob(): void
    {
        if (config('picpay-laravel-aop.tracing.listeners.job.enabled')) {
            Event::listen(JobProcessing::class, config('picpay-laravel-aop.tracing.listeners.job.handler'));
            Event::listen(JobProcessed::class, config('picpay-laravel-aop.tracing.listeners.job.handler'));
            Event::listen(JobFailed::class, config('picpay-laravel-aop.tracing.listeners.job.handler'));
        }
    }
}
