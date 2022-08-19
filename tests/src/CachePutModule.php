<?php

namespace __Test;

class CachePutModule extends \Picpay\LaravelAspect\Modules\CachePutModule
{
    /**
     * @var array
     */
    protected $classes = [
        \__Test\AspectCachePut::class,
    ];
}
