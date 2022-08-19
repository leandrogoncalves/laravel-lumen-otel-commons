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

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Picpay\LaravelAspect\Console\CompileCommand;
use Picpay\LaravelAspect\Console\ClearCacheCommand;
use Picpay\LaravelAspect\Console\ModulePublishCommand;

/**
 * Class ConsoleServiceProvider
 */
class ConsoleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $this->registerCommands();
    }

    /**
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->app->singleton('command.picpay.aspect.clear-cache', function ($app) {
            return new ClearCacheCommand($app['config'], $app['files']);
        });
        $this->app->singleton('command.picpay.aspect.module-publish', function ($app) {
            return new ModulePublishCommand($app['files']);
        });
        $this->app->singleton('command.picpay.aspect.compile', function ($app) {
            return new CompileCommand($app['aspect.manager']);
        });
        $this->commands([
            'command.picpay.aspect.clear-cache',
            'command.picpay.aspect.module-publish',
            'command.picpay.aspect.compile'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            'command.picpay.aspect.clear-cache',
            'command.picpay.aspect.module-publish',
            'command.picpay.aspect.compile'
        ];
    }
}
