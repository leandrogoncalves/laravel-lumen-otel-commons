<?php

namespace Picpay\LaravelAspect\Attribute;

use Attribute as BaseAttribute;
use Ray\Aop\Annotation\AllPublicMethods;

#[BaseAttribute(BaseAttribute::TARGET_ALL)]
class Trace extends AllPublicMethods
{
    public function __construct(
        public string $name = 'Method'
    ) {
    }

}
