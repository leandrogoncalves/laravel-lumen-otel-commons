<?php

namespace Picpay\LaravelAspect\Modules;


use Picpay\LaravelAspect\PointCut\TracePointCut;


class TraceModule extends AspectModule
{
    /** @var array */
    protected $classes = [
    ];

    public function registerPointCut(): TracePointCut
    {
        return new TracePointCut();
    }
}
