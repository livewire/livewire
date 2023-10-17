<?php

namespace Livewire\Features\SupportTesting;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestBroker
{
    use InteractsWithExceptionHandling, MakesHttpRequests;

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function temporarilyDisableExceptionHandlingAndMiddleware($callback)
    {
        $cachedHandler = app(ExceptionHandler::class);

        $cachedShouldSkipMiddleware = $this->app->shouldSkipMiddleware();

        $this->withoutExceptionHandling([HttpException::class, AuthorizationException::class])->withoutMiddleware();

        $result = $callback($this);

        $this->app->instance(ExceptionHandler::class, $cachedHandler);

        if (! $cachedShouldSkipMiddleware) {
            unset($this->app['middleware.disable']);
        }

        return $result;
    }

    public function withoutHandling($except = [])
    {
        return $this->withoutExceptionHandling($except);
    }
}
