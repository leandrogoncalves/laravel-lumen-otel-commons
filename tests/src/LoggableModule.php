<?php

namespace __Test;

use Picpay\LaravelAspect\Modules\LoggableModule as Loggable;

class LoggableModule extends Loggable
{
    /**
     * @var array
     */
    protected $classes = [
        AspectLoggable::class,
        AspectContextualBinding::class,
        AspectMessageDriven::class,
    ];
}
