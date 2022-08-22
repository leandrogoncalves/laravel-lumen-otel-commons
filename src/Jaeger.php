<?php

declare(strict_types=1);

namespace Picpay\LaravelAspect;

use SplStack;
use OpenTracing\Span;
use OpenTracing\Tracer;
use OpenTracing\SpanContext;
use const OpenTracing\Formats\TEXT_MAP;

final class Jaeger
{
    private Tracer $tracer;

    private SplStack $spans;

    private bool $isFinished = false;

    private ?SpanContext $serverContext = null;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
        $this->spans = new \SplStack();
    }

    public function __destruct()
    {
        $this->finish();
    }

    public function tracer(): Tracer
    {
        return $this->tracer;
    }

    public function startWithInject(string $operationName, array &$carrier, array $tags = []): Span
    {
        $span = $this->start($operationName, $tags);

        $this->tracer->inject($span->getContext(), TEXT_MAP, $carrier);

        return $span;
    }

    public function start(string $operationName, array $tags = []): Span
    {
        if ($this->spans->isEmpty()) {
            $span = $this->startSpan($operationName, $this->serverContext);
        } else {
            /** @var Span $parentSpan */
            $parentSpan = $this->spans->top();
            $span       = $this->startSpan($operationName, $parentSpan->getContext());
        }

        if ($tags) {
            foreach ($tags as $key => $value) {
                $span->setTag($key, $value);
            }
        }

        $this->spans->push($span);

        return $span;
    }

    public function stop(string $operationName, array $tags = []): void
    {
        if ($this->spans->isEmpty()) {
            return ;
        }

        $span = $this->spans->top();

        /** @var Span $span */
        if (strcmp($span->getOperationName(), $operationName) === 0) {
            foreach ($tags as $key => $value) {
                $span->setTag($key, $value);
            }
            $span->finish();
            $this->spans->pop();
        }
    }

    public function startStop(string $operationName, array $tags = [], ?float $duration = 0): void
    {
        $currentTime = microtime(true);

        $startTime = $currentTime - $duration;

        if ($this->spans->isEmpty()) {
            $span = $this->startSpan($operationName, $this->serverContext, $startTime);
        } else {
            /** @var Span $parentSpan */
            $parentSpan = $this->spans->top();
            $span       = $this->startSpan($operationName, $parentSpan->getContext(), $startTime);
        }

        if ($tags) {
            foreach ($tags as $key => $value) {
                $span->setTag($key, $value);
            }
        }

        $span->finish((int)($currentTime * 1000000));
    }

    public function inject(array &$carrier): void
    {
        if ($this->getCurrentSpan() === null) {
            throw new \RuntimeException('Can not inject, there is no available span');
        }

        $this->tracer->inject(
            $this->getCurrentSpan()->getContext(),
            TEXT_MAP,
            $carrier,
        );
    }

    public function getCurrentSpan(): ?Span
    {
        if ($this->spans->isEmpty()) {
            return null;
        }

        return $this->spans->top();
    }

    public function initServerContext(array $carrier = null): ?SpanContext
    {
        $this->isFinished = false;

        if (!$carrier) {
            $context = $this->tracer->extract(TEXT_MAP, $_SERVER);
        } else {
            $context = $this->tracer->extract(TEXT_MAP, $carrier);
        }

        $this->serverContext = $context;

        return $this->serverContext;
    }


    public function finish(): void
    {
        if ($this->isFinished) {
            return;
        }

        try {
            $this->finishSpans();
            $this->tracer->flush();
        } catch (\Throwable $e) {
        }

        $this->isFinished = true;
    }

    private function finishSpans(): void
    {
        while (false === $this->spans->isEmpty()) {
            /** @var Span $span */
            $span = $this->spans->pop();

            $span->finish();
        }
    }

    /**
     * @param string $operationName
     * @param SpanContext|null $context
     * @param float|null $startTime
     *
     * @return Span
     */
    private function startSpan(string $operationName, SpanContext $context = null, float $startTime = null): Span
    {
        $options = [];

        if ($context !== null) {
            $options['child_of'] = $context;
        }

        if ($startTime !== null) {
            $options['start_time'] = $startTime;
        }

        return $this->tracer->startSpan($operationName, $options);
    }

    public function getTraceId(): ?string
    {
        if (false === $this->spans->isEmpty()) {
            $spanParent = $this->spans->top();

            if ($spanParent instanceof \Jaeger\Span) {
                /** @psalm-suppress UndefinedInterfaceMethod */
                return $spanParent->getContext()->getTraceId();
            }
        }

        return null;
    }

    public function getRootTraceId(): ?string
    {
        $serverSpan = $this->serverContext;

        if ($serverSpan instanceof \Jaeger\SpanContext) {
            return $serverSpan->getTraceId();
        }

        return null;
    }
}
