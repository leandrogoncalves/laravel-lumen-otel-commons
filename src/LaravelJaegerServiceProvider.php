<?php

declare(strict_types=1);

namespace Picpay\LaravelAspect;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Jaeger\Config;
use OpenTracing\GlobalTracer;

class LaravelJaegerServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->app->singleton(Jaeger::class, static function () {
            $config = new Config(
                config('picpay-laravel-aop.tracing.config'),
                config('picpay-laravel-aop.tracing.service_name'),
            );

            $config->initializeTracer();

            $client = GlobalTracer::get();

            return new Jaeger($client);
        });

        app()->terminating(function () {
            app(Jaeger::class)->finish();
        });

        $this->initHttp();
        $this->initConsole();
        $this->initQuery();
        $this->initJob();
    }

    private function initHttp(): void
    {
        if (config('picpay-laravel-aop.tracing.listeners.http.enabled') && false === $this->app->runningInConsole()) {
            $router = $this->app->get('router');
            $router->middleware(
                config('picpay-laravel-aop.tracing.listeners.http.handler')
            );

            /** @var Kernel $kernel */
            $kernel = $this->app->get(\Illuminate\Contracts\Http\Kernel::class);
            $kernel->pushMiddleware(
                config('picpay-laravel-aop.tracing.listeners.http.handler')
            );
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