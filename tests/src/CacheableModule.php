<?php

namespace __Test;

class CacheableModule extends \Picpay\LaravelAspect\Modules\CacheableModule
{
    /**
     * @var array
     */
    protected $classes = [
        \__Test\AspectCacheable::class,
        \__Test\AspectCacheEvict::class,
        \__Test\AspectMerge::class,
        \ResolveMockClass::class,
    ];
}
