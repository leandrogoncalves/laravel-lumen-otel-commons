# Laravel-Otel-commons
Lib baseada nas libs `jonahgeorge/jaeger-client-php` e `ray/aop`

## Compatibilidade

* PHP >= 8.0
* Composer >= 2
* Laravel >= 9

## Instalação
* Configuração do vcs:
```bash
composer config picpay/laravel-otel-commons vcs https://github.com/PicPay/laravel-otel-commons
```
* Instalação via composer:
```bash
composer require picpay/laravel-otel-commons
```
### Para Lumen
Adicionar os 2 providers abaixo, no arquivo bootstrap/app.php

```bash
$app->register(Picpay\LaravelAspect\LumenAspectServiceProvider::class);
$app->register(Picpay\LaravelAspect\ConsoleServiceProvider::class);
```
### Publicando aspect module class

```bash
php artisan picpay:aspect-module-publish
```
```bash
php artisan vendor:publish --tag=aspect
```
### Registrando aspect module

config/picpay-laravel-aop.php

```php
    'modules' => [
        // append modules
        \YourApplication\Modules\TraceModule::class,
    ],
```

Registrando classes

```php
namespace YourApplication\Modules;

use Picpay\LaravelAspect\Modules\TraceModule as PackageTraceModule;

/**
 * Class TraceModule
 */
class TraceModule extends PackageTraceModule
{
    /** @var array */
    protected $classes = [
        \YourApplication\Services\SampleService::class
    ];
}
```

Exemplo com Trace na classe e com isso é feito trace em todos os métodos públicos 

```php

namespace YourApplication\Services;

use Picpay\LaravelAspect\Attribute\Trace;

#[Trace]
class SampleService
{
    
    public function action($id) 
    {
        return $this;
    }
    
    public function someAction($id) 
    {
        return $this;
    }
}
```

Exemplo com Trace no método

```php

namespace YourApplication\Services;

use Picpay\LaravelAspect\Attribute\Trace;

class SampleService
{
    #[Trace]
    public function action($id) 
    {
        return $this;
    }
}
```

## Cache Clear Command

```bash
php artisan picpay:aspect-clear-cache
```

## PreCompile Command

```bash
php artisan picpay:aspect-compile
```
## Listeners
```php
'listeners' => [
    'http' => [
        'enabled' => env('JAEGER_HTTP_LISTENER_ENABLED', false),
        'handler' => \Picpay\LaravelAspect\JaegerMiddleware::class,
    ],
    'console' => [
        'enabled' => env('JAEGER_CONSOLE_LISTENER_ENABLED', false),
        'handler' => \Picpay\LaravelAspect\Listeners\CommandListener::class,
    ],
    'query' => [
        'enabled' => env('JAEGER_QUERY_LISTENER_ENABLED', false),
        'handler' => \Picpay\LaravelAspect\Listeners\QueryListener::class,
    ],
    'job' => [
        'enabled' => env('JAEGER_JOB_LISTENER_ENABLED', false),
        'handler' => \Picpay\LaravelAspect\Listeners\JobListener::class,
    ],
]
```

## Fazendo Trace Básico

```php
    $jaeger = app(\Picpay\LaravelAspect\Jaeger::class);
    $jaeger->start('Some operation', [
        'tag1' => 'test',
        'tag2' => 'test'
    ]);
    //.....
    $someCode->someFunction();
    //....
    $jaeger->stop('Some operation', [
        'tag3' => 'test',
    ]);

```
