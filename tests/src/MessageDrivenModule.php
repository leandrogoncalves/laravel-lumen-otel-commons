<?php

namespace __Test;

/**
 * Class MessageDrivenModule
 */
class MessageDrivenModule extends \Picpay\LaravelAspect\Modules\MessageDrivenModule
{
    /** @var array  */
    protected $classes = [
        AspectMessageDriven::class,
    ];
}
