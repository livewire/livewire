<?php

namespace Livewire\Features\SupportTesting;

use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

class RequestBroker
{
    use MakesHttpRequests, InteractsWithExceptionHandling;

    protected $app;

    function __construct($app)
    {
        $this->app = $app;
    }

    function temporarilyDisableExceptionHandlingAndMiddleware($callback)
    {
        $cachedHandler = app(ExceptionHandler::class);

        $cachedShouldSkipMiddleware = $this->app->shouldSkipMiddleware();

        $this->withoutExceptionHandling([HttpException::class, AuthorizationException::class])->withoutMiddleware();

        try {
            return app(HandleRequests::class)->temporarilyPropagateExceptions(
                fn () => $callback($this),
            );
        } finally {
            $this->app->instance(ExceptionHandler::class, $cachedHandler);

            if (! $cachedShouldSkipMiddleware) {
                unset($this->app['middleware.disable']);
            }
        }
    }

    function withoutHandling($except = [])
    {
        return $this->withoutExceptionHandling($except);
    }

    function addHeaders(array $headers)
    {
        $this->serverVariables = $this->transformHeadersToServerVars($headers);

        return $this;
    }
}
