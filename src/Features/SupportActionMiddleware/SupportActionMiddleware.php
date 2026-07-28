<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Support\Str;
use Livewire\Attributes\Authorize;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Exceptions\EventHandlerDoesNotExist;
use Livewire\Features\SupportEvents\SupportEvents;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

use function Livewire\{ invade, on, store};

class SupportActionMiddleware extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $context, $earlyReturn, $metadata) {
            if (! app(HandleRequests::class)->isLivewireRoute()) return;

            static::applyActionMiddleware($component, $method, $params);
        });
    }

    // Following how SupportPagination and SupportQueryString set property attribute,
    // any authorize middleware will be convert into `#[Authorize]` attribute for that method
    // See: HandlesAttributes::setMethodAttribute()
    function boot()
    {
        if (! app(HandleRequests::class)->isLivewireRoute()) return;

        $middlewareAttributes = $this->storeGet('middlewareAttributes', []);

        $filtered = [];
        foreach ($middlewareAttributes as $method => $attributeArguments) {
            $resolved = app('router')->resolveMiddleware($attributeArguments);

            foreach ($resolved as $middleware) {
                if (str_starts_with($middleware, AuthorizeMiddleware::class)) {
                    [$ability, $arguments] = $this->parseMiddleware($middleware);

                    $attribute = new Authorize($ability, $arguments);

                    $this->component->setMethodAttribute($method, $attribute);

                    continue;
                }

                $filtered[$method][] = $middleware;
            }
        }

        $this->storeSet('middlewareAttributes', $filtered);
    }

    protected static function applyActionMiddleware($component, $method, $params)
    {
        $method = static::resolveMethodName($component, $method, $params);

        // Return early if there is no middleware attribute on called method
        if (! $actionMiddleware = store($component)->find('middlewareAttributes', $method)) return;

        [$request, $middleware] = static::filterMiddleware($actionMiddleware);

        if (empty($middleware)) return;

        // Gather all action middleware from method and apply it all at once
        Utils::applyMiddleware($request, $middleware);
    }

    protected static function resolveMethodName($component, $method, $params)
    {
        if ($method === '__dispatch') {
            [$name, $params] = $params;

            $names = SupportEvents::getListenerEventNames($component);

            if (! in_array($name, $names)) {
                throw new EventHandlerDoesNotExist($name);
            }

            return SupportEvents::getListenerMethodName($component, $name);
        }

        return $method;
    }

    protected static function filterMiddleware(array $middleware): array
    {
        $mechanism = invade(app(PersistentMiddleware::class));

        $request = $mechanism->makeFakeRequest();

        $excludedMiddleware = $mechanism->applicableMiddleware;

        // Exclude any middleware that has been applied by PersistentMiddleware.
        // If middleware not registered as persistent middleware and applied
        // on both route level and attribute, it will runs twice as intended behavior
        // because middleware attribute only applied on subsequent request that hit Livewire update endpoint.
        $resolved = collect($middleware)
            ->reject(function ($value, $key) use ($excludedMiddleware) {
                return collect($excludedMiddleware)->contains(function ($iValue, $iKey) use ($value) {
                    // Some middlewares can be closures.
                    if (! is_string($value)) return true;

                    // Ensure any middleware arguments aren't included in the comparison
                    return Str::before($value, ':') == $iValue;
                });
            })
            ->values()
            ->all();

        return [$request, $resolved];
    }

    protected function parseMiddleware($middleware)
    {
        [$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        $ability = array_shift($parameters);

        $arguments = empty($parameters) ? null : $parameters;

        return [$ability, $arguments];
    }
}