<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Support\Str;
use Livewire\Exceptions\CannotBindDataToEloquenModelException;
use Livewire\Exceptions\MissingComponentMethodReferencedByAction;
use Livewire\Exceptions\NonPublicComponentMethodCall;
use Livewire\Exceptions\ProtectedPropertyBindingException;

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

        throw_if(in_array($propertyName, $this->lockedModelProperties), new CannotBindDataToEloquenModelException($name));

        $this->callBeforeAndAferSyncHooks($name, $value, function ($name, $value) use ($propertyName) {
            // @todo: this is fired even if a property isn't present at all which is confusing.
            throw_unless($this->propertyIsPublicAndNotDefinedOnBaseClass($propertyName), ProtectedPropertyBindingException::class);

            if ($this->containsDots($name)) {
                data_set($this->{$propertyName}, $this->afterFirstDot($name), $value);
            }

            $this->{$name} = $value;

            $this->rehashProperty($name);
        });
    }

    protected function callBeforeAndAferSyncHooks($name, $value, $callback)
    {
        $beforeMethod = 'updating'.Str::studly($name);
        $afterMethod = 'updated'.Str::studly($name);

        if (method_exists($this, $beforeMethod)) {
            $this->{$beforeMethod}($value);
        }

        $callback($name, $value);

        if (method_exists($this, $afterMethod)) {
            $this->{$afterMethod}($value);
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
                throw_unless(method_exists($this, $method), MissingComponentMethodReferencedByAction::class);
                throw_unless($this->methodIsPublicAndNotDefinedOnBaseClass($method), new NonPublicComponentMethodCall($method));

                $this->{$method}(
                    ...$this->resolveActionParameters($method, $params)
                );

                break;
        }
    }

    protected function resolveActionParameters($method, $params)
    {
        return collect((new \ReflectionMethod($this, $method))->getParameters())->map(function ($parameter) use (&$params) {
            return rescue(function () use ($parameter) {
                if ($class = $parameter->getClass()) {
                    return app($class->name);
                }

                return app($parameter->name);
            }, function () use (&$params) {
                return array_shift($params);
            }, false);
        });
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
