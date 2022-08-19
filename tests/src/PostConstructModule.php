<?php

namespace __Test;

/**
 * Class PostConstructModule
 */
class PostConstructModule extends \Picpay\LaravelAspect\Modules\PostConstructModule
{
    protected $classes = [
        AspectPostConstruct::class,
    ];
}
