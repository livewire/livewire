<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Support\Str;
use Livewire\Exceptions\NonPublicComponentMethodCall;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Exceptions\CannotBindDataToEloquentModelException;
use Livewire\Exceptions\MethodNotFoundException;

trait HandlesActions
{
    protected $lockedModelProperties = [];

    public function lockPropertyFromSync($property)
    {
        $this->lockedModelProperties[] = $property;
    }

    public function syncInput($name, $value)
    {
        $propertyName = $this->beforeFirstDot($name);

        throw_if(in_array($propertyName, $this->lockedModelProperties), new CannotBindDataToEloquentModelException($name));

        $this->callBeforeAndAferSyncHooks($name, $value, function ($name, $value) use ($propertyName) {
            // @todo: this is fired even if a property isn't present at all which is confusing.
            throw_unless(
                $this->propertyIsPublicAndNotDefinedOnBaseClass($propertyName),
                new PublicPropertyNotFoundException($propertyName, $this->getName())
            );

            if ($this->containsDots($name)) {
                data_set($this->{$propertyName}, $this->afterFirstDot($name), $value);
            } else {
                $this->{$name} = $value;
            }

            $this->rehashProperty($name);
        });
    }

    protected function callBeforeAndAferSyncHooks($name, $value, $callback)
    {
        $propertyName = Str::before(Str::studly($name), '.');
        $keyAfterFirstDot = Str::contains($name, '.') ? Str::after($name, '.') : null;

        $beforeMethod = 'updating'.$propertyName;
        $afterMethod = 'updated'.$propertyName;

        if (method_exists($this, $beforeMethod)) {
            $this->{$beforeMethod}($value, $keyAfterFirstDot);
        }

        $callback($name, $value);

        if (method_exists($this, $afterMethod)) {
            $this->{$afterMethod}($value, $keyAfterFirstDot);
        }
    }

    public function callMethod($method, $params = [])
    {
        switch ($method) {
            case '$set':
                $prop = array_shift($params);
                $this->syncInput($prop, head($params));

                return;
                break;

            case '$toggle':
                $prop = array_shift($params);
                $this->syncInput($prop, ! $this->{$prop});

                return;
                break;

            case '$refresh':
                return;
                break;

            default:
                throw_unless(method_exists($this, $method), new MethodNotFoundException($method, $this->getName()));
                throw_unless($this->methodIsPublicAndNotDefinedOnBaseClass($method), new NonPublicComponentMethodCall($method));

                app()->call([$this, $method], $this->resolveActionParameters($method, $params));

                break;
        }
    }

    protected function resolveActionParameters($method, $params)
    {
        return collect((new \ReflectionMethod($this, $method))->getParameters())->flatMap(function ($parameter) use (&$params) {
            if (!$parameter->getClass()) {
                return [$parameter->getName() => array_shift($params)];
            }

            $instance = rescue(function () use ($class) { return app($class->name); });
            if ($instance instanceof UrlRoutable) {
                if ($model = $instance->resolveRouteBinding(array_shift($params))) {
                    return [$parameter->getName() => $model];
                };
            }
        })->filter()->all();
    }

    protected function methodIsPublicAndNotDefinedOnBaseClass($methodName)
    {
        return collect((new \ReflectionClass($this))->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->reject(function ($method) {
                // The "render" method is a special case. This method might be called by event listeners or other ways.
                if ($method === 'render') {
                    return false;
                }

                return $method->class === self::class;
            })
            ->pluck('name')
            ->search($methodName) !== false;
    }
}
