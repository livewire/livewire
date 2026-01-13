<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;

use Livewire\Mechanisms\Mechanism;

use function Livewire\trigger;

class HandleRequests extends Mechanism
{
    protected $updateRoute;

    function boot()
    {
        // Only set it if another provider hasn't already set it....
        if (! $this->updateRoute) {
            app($this::class)->setUpdateRoute(function ($handle) {
                return Route::post('/livewire/update', $handle)->middleware('web');
            });
        }

        $this->skipRequestPayloadTamperingMiddleware();
    }

    function getUpdateUri()
    {
        return (string) str(
            route($this->updateRoute->getName(), [], false)
        )->start('/');
    }

    function skipRequestPayloadTamperingMiddleware()
    {
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::skipWhen(function () {
            return $this->isLivewireRequest();
        });

        \Illuminate\Foundation\Http\Middleware\TrimStrings::skipWhen(function () {
            return $this->isLivewireRequest();
        });
    }

    function setUpdateRoute($callback)
    {
        $route = $callback([self::class, 'handleUpdate']);

        // Append `livewire.update` to the existing name, if any.
        if (! str($route->getName())->endsWith('livewire.update')) {
            $route->name('livewire.update');
        }

        $this->updateRoute = $route;
    }

    function isLivewireRequest()
    {
        return request()->hasHeader('X-Livewire');
    }

    function isLivewireRoute()
    {
        // @todo: Rename this back to `isLivewireRequest` once the need for it in tests has been fixed.
        $route = request()->route();

        if (! $route) return false;

        /*
         * Check to see if route name ends with `livewire.update`, as if
         * a custom update route is used and they add a name, then when
         * we call `->name('livewire.update')` on the route it will
         * suffix the existing name with `livewire.update`.
         */
        return $route->named('*livewire.update');
    }

    function handleUpdate()
    {
        $requestPayload = request(key: 'components', default: []);
        
        $finish = trigger('request', $requestPayload);

        $requestPayload = $finish($requestPayload);

        $componentResponses = [];

        foreach ($requestPayload as $componentPayload) {
            $snapshot = json_decode($componentPayload['snapshot'], associative: true);
            $updates = $componentPayload['updates'];
            $calls = $componentPayload['calls'];

            [ $snapshot, $effects ] = app('livewire')->update($snapshot, $updates, $calls);

            $componentResponses[] = [
                'snapshot' => json_encode($snapshot),
                'effects' => $effects,
            ];
        }

        $responsePayload = [
            'components' => $componentResponses,
            'assets' => SupportScriptsAndAssets::getAssets(),
        ];

        $finish = trigger('response', $responsePayload);

        $response = $finish($responsePayload);

        /**
         * When wire:stream is used, headers are sent early by SupportStreaming::ensureStreamResponseStarted().
         * The streaming content has already been output via echo/flush in SupportStreaming::streamContent().
         * This final JSON response contains the component snapshot and must be output without attempting
         * to send additional headers, which would cause "headers already sent" warnings (since Symfony 7.2.7).
         *
         * We detect if headers have already been sent and return a StreamedResponse that prevents
         * any further header modification attempts while still outputting the response body.
         *
         * @see StreamedResponse
         * @see \Livewire\Features\SupportStreaming\SupportStreaming::ensureStreamResponseStarted()
         * @see https://github.com/symfony/symfony/issues/60603
         * @see https://github.com/livewire/livewire/issues/9357
         */
        if (headers_sent()) {
            // Encode the response to JSON, with error handling
            $jsonResponse = json_encode($response);

            if ($jsonResponse === false) {
                throw new \RuntimeException(
                    'Failed to encode Livewire response to JSON: ' . json_last_error_msg()
                );
            }

            // Return a response that won't attempt to send headers
            // Headers are still set on the object for consistency, even though they won't be sent
            return new StreamedResponse(
                $jsonResponse,
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $response;
    }
}
