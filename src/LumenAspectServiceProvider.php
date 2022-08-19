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

use Picpay\LaravelAspect\AspectServiceProvider as AspectProvider;

/**
 * Class LumenAspectServiceProvider
 *
 * @codeCoverageIgnore
 */
class LumenAspectServiceProvider extends AspectProvider
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
}