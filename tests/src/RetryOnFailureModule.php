<?php

namespace __Test;

/**
 * Class RetryOnFailureModule
 */
class RetryOnFailureModule extends \Picpay\LaravelAspect\Modules\RetryOnFailureModule
{
    /** @var array  */
    protected $classes = [
        AspectRetryOnFailure::class,
    ];
}
