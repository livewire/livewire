<?php

namespace Livewire\Features\SupportTesting;

use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Testing\Fakes\ExceptionHandlerFake;
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

        // A faked handler is not rebound when exception handling is disabled: its inner handler is swapped instead.
        $cachedInnerHandler = $cachedHandler instanceof ExceptionHandlerFake ? $cachedHandler->handler() : null;

        $cachedShouldSkipMiddleware = $this->app->shouldSkipMiddleware();

        $this->withoutExceptionHandling([HttpException::class, AuthorizationException::class, ModelNotFoundException::class])->withoutMiddleware();

        try {
            return app(HandleRequests::class)->temporarilyPropagateExceptions(
                fn () => $callback($this),
            );
        } finally {
            if ($cachedInnerHandler) {
                $cachedHandler->setHandler($cachedInnerHandler);
            }

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
