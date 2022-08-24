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
        $span = $trace->start($invocation->getMethod()->class.'::'.$invocation->getMethod()->getName());
        $span->setTag('method', $invocation->getMethod()->getName());
        $parameters = $invocation->getMethod()->getParameters();
        $array = array_values($invocation->getArguments()->getArrayCopy());
        try {
            foreach ($array as $key => $item) {
                if (is_object($item)) {
                    if (method_exists($item, 'toArray')) {
                        $span->setTag('method.param.'.$parameters[$key]->name, json_encode($item->toArray()));
                        $span->log($item->toArray());
                    } elseif (method_exists($item, '__toString')) {
                        $span->setTag('method.param.'.$parameters[$key]->name, $item->__toString());
                    }
                    continue;
                }
                if (is_array($item)) {
                    $span->setTag('method.param.'.$parameters[$key]->name, json_encode($item));
                    $span->log($item);
                    continue;
                }
                $span->setTag('method.param.'.$parameters[$key]->name, (string)$item ?? null);
            }
            $span->log($invocation->getMethod()->getParameters());
            $span->log($invocation->getMethod()->getAttributes());
            $result = $invocation->proceed();
            $this->formatReturn($span, $result);
        } catch (\Exception $e) {
            $span->setTag('error', true);
            $span->setTag('error.msg', $e->getMessage());
            $span->setTag('error.code', $e->getCode());
            $span->setTag('error.file', $e->getFile());
            $span->setTag('error.line', $e->getLine());
            $span->setTag('error.stack', $e->getTraceAsString());
            $span->log($e->getTrace());

            throw $e;
        } finally {
            $trace->stop($span->getOperationName());
        }

        return $result;
    }

    private function formatReturn($span, $return)
    : void {
        if (is_object($return)) {
            if (method_exists($return, 'toArray')) {
                $span->setTag('method.return', json_encode($return->toArray()));
                $span->log($return->toArray());
            } elseif (method_exists($return, '__toString')) {
                $span->setTag('method.return', $return->__toString());
            }
        } elseif (is_array($return)) {
            $span->setTag('method.return', json_encode($return));
            $span->log($return);
        } else {
            $span->setTag('method.return', (string)$return ?? null);
        }
    }
}
