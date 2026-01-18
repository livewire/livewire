<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Exceptions\PayloadTooLargeException;
use Livewire\Exceptions\TooManyComponentsException;

use Livewire\Mechanisms\Mechanism;

use function Livewire\trigger;

class HandleRequests extends Mechanism
{
    protected $updateRoute;

    function boot()
    {
        // Only set it if another provider or routes file haven't already set it....
        app()->booted(function () {
            // Check both instance state and router to handle cached routes scenario.
            // When routes are cached and loaded, $this->updateRoute will be null but
            // the route already exists in the router. This prevents duplicate registration
            // which Laravel v12.29.0+ treats as an error.
            if (! $this->updateRoute && ! $this->updateRouteExists()) {
                app($this::class)->setUpdateRoute(function ($handle) {
                    return Route::post(EndpointResolver::updatePath(), $handle)->middleware('web');
                });
            }
        });

        $this->skipRequestPayloadTamperingMiddleware();
    }

    protected function updateRouteExists()
    {
        // Check if a route with name ending in 'livewire.update' already exists.
        // Custom routes can have prefixes (e.g., 'tenant.livewire.update') so we
        // need to check for routes ending with 'livewire.update', not just exact matches.
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (str($route->getName())->endsWith('livewire.update')) {
                return true;
            }
        }

        return false;
    }

    function getUriPrefix()
    {
        return EndpointResolver::prefix();
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
        // Check payload size limit...
        $maxSize = config('livewire.payload.max_size');

        if ($maxSize !== null) {
            $contentLength = request()->header('Content-Length', 0);

            if ($contentLength > $maxSize) {
                throw new PayloadTooLargeException($contentLength, $maxSize);
            }
        }

        $requestPayload = request(key: 'components', default: []);

        // Check max components limit...
        $maxComponents = config('livewire.payload.max_components');

        if ($maxComponents !== null && count($requestPayload) > $maxComponents) {
            throw new TooManyComponentsException(count($requestPayload), $maxComponents);
        }

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

        $payload = $finish($responsePayload);

        // When wire:stream is used, headers are sent early by SupportStreaming::ensureStreamResponseStarted().
        // The streaming content has already been output via echo/flush in SupportStreaming::streamContent().
        // This final JSON response contains the component snapshot and must be output without attempting
        // to send additional headers, which would cause "headers already sent" warnings (since Symfony 7.2.7).
        if (headers_sent()) {
            $response = new StreamedResponse(
                json_encode($payload),
                200,
                ['Content-Type' => 'application/json']
            );

            // Headers won't be sent due to override, but are documented on the object
            return $response;
        }

        return $payload;
    }
}
