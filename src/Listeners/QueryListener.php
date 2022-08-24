<?php

declare(strict_types=1);

namespace Picpay\LaravelAspect\Listeners;

use Picpay\LaravelAspect\Jaeger;
use Illuminate\Database\Events\QueryExecuted;

final class QueryListener
{
    private Jaeger $jaeger;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }

    public function handle(QueryExecuted $event): void
    {
        $this->jaeger->startStop("DB Query: $event->sql", [
            'query.sql' => $event->sql,
            'query.time' => $event->time,
        ], $event->time / 1000);
    }
}
