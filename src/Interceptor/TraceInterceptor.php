<?php

namespace Picpay\LaravelAspect\Interceptor;

use Picpay\LaravelAspect\Annotation\AnnotationReaderTrait;
use Picpay\LaravelAspect\Jaeger;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;


class TraceInterceptor extends AbstractLogger implements MethodInterceptor
{
    use AnnotationReaderTrait;

    public function invoke(MethodInvocation $invocation)
    {
        /** @var Jaeger $trace */
        $trace = app(Jaeger::class);
        $span = $trace->start($invocation->getMethod()->getName());
        $span->setTag('method', $invocation->getMethod()->getName());
        $span->log(['method' => $invocation->getMethod()->getName()]);
        $parameters = $invocation->getMethod()->getParameters();
        $array = array_values($invocation->getArguments()->getArrayCopy());
        try {
            foreach ($parameters as $k => $parameter) {
                $span->setTag(
                    $parameter->getName(),
                    'test'
                );
            }
            $result = $invocation->proceed();

        } catch (\Exception $e) {
            $span->setTag('error', true);
            $span->setTag('error.msg', $e->getMessage());
            $span->setTag('error.code', $e->getCode());
            $span->setTag('error.file', $e->getFile());
            $span->setTag('error.line', $e->getLine());
            $span->setTag('error.stack', $e->getTraceAsString());
            $span->log($e->getTrace());

            throw $e;
        }

        return $result;
    }
}
