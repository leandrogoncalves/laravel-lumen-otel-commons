<?php

namespace Picpay\LaravelAspect\Interceptor;

use OpenTracing\GlobalTracer;
use Picpay\LaravelAspect\Annotation\AnnotationReaderTrait;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;


class TraceInterceptor extends AbstractLogger implements MethodInterceptor
{
    use AnnotationReaderTrait;

    public function invoke(MethodInvocation $invocation)
    {
        $span = GlobalTracer::get()->startActiveSpan($invocation->getMethod()->getName(), [
            'child_of' => GlobalTracer::get()->getActiveSpan(),
        ]);

        $span->getSpan()->setTag('method', $invocation->getMethod()->getName());
        $span->getSpan()->log(['method' => $invocation->getMethod()->getName()]);
        $parameters = $invocation->getMethod()->getParameters();
        $array = array_values($invocation->getArguments()->getArrayCopy());
        try {
            foreach ($parameters as $k => $parameter) {
                $span->getSpan()->setTag(
                    $parameter->getName(),
                    serialize($array[$k])
                );
            }
            $result = $invocation->proceed();

        } catch (\Exception $e) {
            $span->getSpan()->setTag('error', true);
            $span->getSpan()->setTag('error.msg', $e->getMessage());
            $span->getSpan()->setTag('error.code', $e->getCode());
            $span->getSpan()->setTag('error.file', $e->getFile());
            $span->getSpan()->setTag('error.line', $e->getLine());
            $span->getSpan()->setTag('error.stack', $e->getTraceAsString());

            throw $e;
        } finally {
            $span->close();
        }

        return $result;
    }
}
