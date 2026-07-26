<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Exceptions\EventHandlerDoesNotExist;
use Livewire\Features\SupportAuthorization\BaseAuthorize;
use Livewire\Features\SupportEvents\SupportEvents;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

use function Livewire\invade;
use function Livewire\on;
use function Livewire\store;

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
        $middlewareAttributes = $this->storeGet('middlewareAttributes', []);

        foreach ($middlewareAttributes as $method => $attributeArguments) {
            foreach ($attributeArguments as $key => $middleware) {
                if ($this->isAuthorizeMiddleware($middleware)) {
                    unset($middlewareAttributes[$method][$key]);

                    [$ability, $argument] = $this->parseMiddleware($middleware);

                    $attribute = new BaseAuthorize($ability, $argument);

                    $this->component->setMethodAttribute($method, $attribute);
                }
            }
        }

        $this->storeSet('middlewareAttributes', $middlewareAttributes);
    }

    protected static function applyActionMiddleware($component, $method, $params)
    {
        $method = static::resolveMethodName($component, $method, $params);

        // Return early if there is no middleware attribute on called method
        if (! $actionMiddleware = store($component)->find('middlewareAttributes', $method)) return;

        [$request, $resolved] = static::resolveMiddleware($actionMiddleware);

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

    protected static function resolveMiddleware(array $middleware): array
    {
        $mechanism = invade(app(PersistentMiddleware::class));

        $request = $mechanism->makeFakeRequest();

        $excludedMiddleware = $mechanism->getApplicablePersistentMiddleware($request);

        // Since PersistentMiddleware runs first, 
        // we need to exclude any middleware that has been applied from it
        $resolved = collect(app('router')->resolveMiddleware($middleware, $excludedMiddleware))
            ->filter(fn ($m) => is_string($m))
            ->values()
            ->all();

        return [$request, $resolved];
    }

    protected function isAuthorizeMiddleware($middleware)
    {
        $name = explode(':', $middleware, 2)[0];

        return $name === 'can' || $name === AuthorizeMiddleware::class;
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