<?php

namespace Picpay\LaravelAspect\PointCut;

use Illuminate\Contracts\Container\Container;
use Picpay\LaravelAspect\Attribute\Trace;
use Picpay\LaravelAspect\Interceptor\TraceInterceptor;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;

class TracePointCut extends CommonPointCut implements PointCutable
{
    /** @var string */
    protected $annotation = Trace::class;

    /**
     * @param Container $app
     *
     * @return Pointcut
     */
    public function configure(Container $app): Pointcut
    {
        $interceptor = new TraceInterceptor();
        $this->setInterceptor($interceptor);

        return $this->withAnnotatedAnyInterceptor();
    }

    protected function withAnnotatedAnyInterceptor(): PointCut
    {
        if (method_exists($this->interceptor, 'setAnnotation')) {
            $this->interceptor->setAnnotation($this->annotation);
        }

        return new Pointcut(
            (new Matcher())->any(),
            (new Matcher())->annotatedWith($this->annotation),
            [$this->interceptor]
        );
    }
}
