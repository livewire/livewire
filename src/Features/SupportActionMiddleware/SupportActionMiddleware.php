<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
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

        $resolved = [];
        foreach ($middlewareAttributes as $method => $attributeArguments) {
            $resolved[$method] = app('router')->resolveMiddleware($attributeArguments);
        }

        $filtered = [];
        foreach ($resolved as $method => $values) {
            foreach ($values as $middleware) {
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

        [$request, $resolved] = static::filterMiddleware($actionMiddleware);

        if (empty($resolved)) return;

        // Gather all action middleware from method and apply it all at once
        Utils::applyMiddleware($request, $resolved);
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

        $excludedMiddleware = $mechanism->getApplicablePersistentMiddleware($request);

        // Exclude any middleware that has been applied on route level
        $resolved = array_diff($middleware, $excludedMiddleware);

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