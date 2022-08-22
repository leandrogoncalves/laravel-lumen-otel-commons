<?php

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
        'sampler' => [
            'type' => Jaeger\SAMPLER_TYPE_CONST,
            'param' => true,
        ],
        'logging' => true,
        "tags" => [
            // process. prefix works only with JAEGER_OVER_HTTP, JAEGER_OVER_BINARY
            // otherwise it will be shown as simple global tag
            "process.process-tag-key-1" => "process-value-1", // all tags with `process.` prefix goes to process section
            "process.process-tag-key-2" => "process-value-2", // all tags with `process.` prefix goes to process section
            "global-tag-key-1" => "global-tag-value-1", // this tag will be appended to all spans
            "global-tag-key-2" => "global-tag-value-2", // this tag will be appended to all spans
        ],
        "local_agent" => [
            "reporting_host" => "localhost",
//        You can override port by setting local_agent.reporting_port value
//        "reporting_port" => 6832
        ],
//     Different ways to send data to Jaeger. Config::ZIPKIN_OVER_COMPACT - default):
        'dispatch_mode' => Jaeger\Config::JAEGER_OVER_BINARY_UDP,
    ],
];
