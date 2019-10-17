<?php

namespace Livewire\Concerns;

use Illuminate\Support\Str;
use Livewire\Exceptions\NonPublicComponentMethodCall;
use Livewire\Exceptions\MissingComponentMethodReferencedByAction;

trait HandlesActions
{
    public function syncInput($name, $value)
    {
        $this->callBeforeAndAferSyncHooks($name, $value, function ($name, $value) {
            $this->setPropertyValue($name, $value);

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
                throw_unless($this->methodIsPublicAndNotDefinedOnBaseClass($method), NonPublicComponentMethodCall::class);

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
                return $method->class === self::class;
            })
            ->pluck('name')
            ->search($methodName) !== false;
    }
}
