<?php

declare(strict_types=1);

namespace Picpay\LaravelAspect;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

use function in_array;

final class JaegerMiddleware
{
    private Jaeger $jaeger;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }

    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    : mixed {
        try {
            $route = Route::getRoutes()->match($request);

            if ($route->isFallback) {
                return $next($request);
            }

            $uri = $route->uri();
        } catch (\Throwable $e){
            $uri = $request->getRequestUri();
        }
        $httpMethod = $request->method();
        
        $deleteRoutes = config('picpay-laravel-aop.tracing.listeners.http.delete_routes') ?? [];
        if (in_array($uri, $deleteRoutes)) {
            return $next($request);
        }
        $headers = [];

        foreach ($request->headers->all() as $key => $value) {
            $headers[$key] = Arr::first($value);
        }

        $jaeger = $this->jaeger;

        $jaeger->initServerContext($headers);
        $span = $jaeger->start("$httpMethod: /$uri", [
            'http.scheme' => $request->getScheme(),
            'http.ip_address' => $request->ip(),
            'http.host' => $request->getHost(),
            'laravel.version' => app()->version(),
            'kind' => 'server',
            'span.kind' => 'server',
        ]);

        /** @var Response $response */
        $response = $next($request);
        if ($response->isServerError() || $response->isClientError()) {
            $span->setTag('Status', 'ERROR');
            $span->setTag('otel.status_code', 'ERROR');
            $span->setTag('otel.status_description', $response->getContent());
            $span->setTag('error', true);
        } else {
            $span->setTag('otel.status_code', 'OK');
            $span->setTag('Status', 'OK');
        }

        return $response;
    }
}
