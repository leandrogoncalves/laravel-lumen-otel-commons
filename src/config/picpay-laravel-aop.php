<?php


return [

    'aspect' => [
        /**
         * choose aop library
         * "ray"(Ray.Aop), "none"(for testing)
         */
        'default' => env('ASPECT_DRIVER', 'ray'),

        /**
         * for aspect driver options
         */
        'drivers'     => [
            'ray'  => [
                // If set to true, compile classes each time
                'force_compile' => env('ASPECT_FORCE_COMPILE', false),
                // string Path to the compiled directory where compiled classes will be stored
                'compile_dir' => storage_path('framework/aop/compile'),
                // aspect kernel cacheable
                'cache' => env('ASPECT_CACHEABLE', false),
                // string Path to the cache file directory where cache classes will be stored
                'cache_dir' => storage_path('framework/aop/cache'),
            ],
            'none' => [
                // for testing driver
                // no use aspect
            ]
        ],
        'modules' => [
            // append modules
            // \App\Modules\CacheableModule::class,
        ],
    ],

    'annotation' => [
        'ignores' => [
            // global Ignored Annotations
            'Hears',
            'Get',
            'Post',
            'Put',
            'Patch',
            'Options',
            'Delete',
            'Any',
            'Middleware',
            'Resource',
            'Controller'
        ],
        'custom' => [
            // added your annotation class
        ],
    ],
    'tracing' =>[
        'service_name' => env('JAEGER_SERVICE_NAME', env('APP_NAME', 'Laravel')),

        'config' => [
            'sampler' => [
                'type'  => \Jaeger\SAMPLER_TYPE_CONST,
                'param' => env('JAEGER_SAMPLE_RATE', true),
            ],
            'local_agent' => [
                'reporting_host' => env('JAEGER_HOST', 'jaeger'),
                'reporting_port' => env('JAEGER_PORT', 14268),
            ],
            'dispatch_mode' => \Jaeger\Config::JAEGER_OVER_BINARY_HTTP,
        ],

        'listeners' => [
            'http' => [
                'enabled' => env('JAEGER_HTTP_LISTENER_ENABLED', false),
                'handler' => \Picpay\LaravelAspect\JaegerMiddleware::class,
                'delete_routes' => []
            ],
            'console' => [
                'enabled' => env('JAEGER_CONSOLE_LISTENER_ENABLED', false),
                'handler' => \Picpay\LaravelAspect\Listeners\CommandListener::class,
            ],
            'query' => [
                'enabled' => env('JAEGER_QUERY_LISTENER_ENABLED', false),
                'handler' => \Picpay\LaravelAspect\Listeners\QueryListener::class,
            ],
            'job' => [
                'enabled' => env('JAEGER_JOB_LISTENER_ENABLED', false),
                'handler' => \Picpay\LaravelAspect\Listeners\JobListener::class,
            ],
        ],
    ],
];
