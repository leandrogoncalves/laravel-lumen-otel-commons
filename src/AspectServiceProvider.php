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

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\ServiceProvider;
use Jaeger\Config;
use OpenTracing\GlobalTracer;
use OpenTracing\Tracer;

/**
 * Class AspectServiceProvider
 */
class AspectServiceProvider extends ServiceProvider
{
    /** @var bool */
    protected $defer = false;

    /**
     * boot aspect kernel
     */
    public function boot(): void
    {
        $this->app['aspect.manager']->weave();

        if ($this->app['config']['tracing.errors']) {
            $this->app['events']->listen(MessageLogged::class, function (MessageLogged $event) {
                if ($event->level == 'error') {
                    optional(GlobalTracer::get()->getActiveSpan())->setTag('error', 'true');
                    optional(GlobalTracer::get()->getActiveSpan())->tag('error_message', $event->message);
                }
            });
        }

        if (method_exists($this->app, 'terminating')) {
            $this->app->terminating(function () {
                optional(GlobalTracer::get()->getActiveSpan())->finish();
                GlobalTracer::get()->flush();
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        /**
         * for package configure
         */
        $configPath = __DIR__ . '/config/picpay-laravel-aop.php';
        $this->mergeConfigFrom($configPath, 'picpay-laravel-aop');
        $this->publishes([$configPath => config_path('picpay-laravel-aop.php')], 'aspect');

        $this->app->singleton(AnnotationConfiguration::class, function ($app) {
            $annotationConfiguration = new AnnotationConfiguration(
                $app['config']->get('picpay-laravel-aop.annotation')
            );

            return $annotationConfiguration;
        });
        $this->app->singleton('aspect.manager', function ($app) {
            /** @var AnnotationConfiguration $annotationConfiguration */
            $annotationConfiguration = $app->make(AnnotationConfiguration::class);
            $annotationConfiguration->ignoredAnnotations();

            // register annotation
            return new AspectManager($app);
        });
        $this->app->singleton(Config::class, function ($app) {
            $config = new Config(
                $app['config']->get('picpay-laravel-aop.tracing'),
                env('APP_NAME', 'app-name'),
            );
            $config->initializeTracer();
            return $config;
        });
        $this->app->singleton(Tracer::class, function ($app) {
            $app->make(Config::class);
            return GlobalTracer::get();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            'aspect.manager',
        ];
    }
}
