<?php

namespace Livewire\Features\SupportActionMiddleware;

use Attribute;
use Illuminate\Auth\Middleware\Authorize as AuthorizeMiddleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\ImplicitlyBoundMethod;

use function Illuminate\Support\enum_value;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BaseMiddleware extends LivewireAttribute
{
    use AuthorizesRequests;
    
    public function __construct(public string $middleware)
    {
        //
    }

    public function call(array $parameters)
    {
        $middleware = app('router')->resolveMiddleware([$this->middleware]);

        if ($middleware === []) return;

        $authorizeMiddleware = Arr::first($middleware, function ($m) {
            return Str::before($m, ':') === AuthorizeMiddleware::class;
        });

        if ($authorizeMiddleware) {
            $this->handleAuthorizeMiddleware($authorizeMiddleware, $parameters);

            return;
        }

        Utils::applyMiddleware(request(), $middleware);
    }

    protected function handleAuthorizeMiddleware($middleware, $parameters)
    {
        $arguments = $this->parseMiddleware($middleware);

        $ability = array_shift($arguments);

        $methodDependencies = null;
        $resolveMethodDependencies = function () use (&$methodDependencies, $parameters): array {
            return $methodDependencies ??= ImplicitlyBoundMethod::resolveMethodDependencies(
                app(),
                [$this->component, $this->getName()],
                $parameters,
            );
        };

        $resolved = [];
        foreach ($arguments as $arg) {
            $resolved[] = $this->resolveArgument($arg, $parameters, $resolveMethodDependencies);
        }

        $this->authorize($ability, $resolved);
    }

    protected function resolveArgument(string $arg, array $parameters, \Closure $resolveMethodDependencies): mixed
    {
        // Action that does not require a model, for example a 'create' action...
        if (class_exists($arg)) {
            return $arg;
        }

        // Try method parameter first (prioritized per rules)
        $methodArgument = Arr::first(
            (new \ReflectionObject($this->component))->getMethod($this->getName())->getParameters(),
            fn (\ReflectionParameter $parameter) : bool => $parameter->getName() === $arg,
        );

        if ($methodArgument instanceof \ReflectionParameter) {
            $methodDependencies = $resolveMethodDependencies();

            return $methodDependencies['named'][$arg];
        }

        // Fall back to component property
        return data_get($this->component, $arg);
    }

    protected function parseMiddleware($middleware)
    {
        [$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return $parameters;
    }

    protected function parseAbilityAndArguments($ability, $arguments)
    {
        $ability = enum_value($ability);

        if (is_string($ability) && ! str_contains($ability, '\\')) {
            return [$ability, $arguments];
        }

        return [$this->normalizeGuessedAbilityName($this->getName()), $ability];
    }
}