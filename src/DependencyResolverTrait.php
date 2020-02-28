<?php

namespace Livewire;

use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use Illuminate\Contracts\Container\BindingResolutionException;

trait DependencyResolverTrait
{
    protected function resolveClassMethodDependencies(array $parameters, $instance, $method)
    {
        if (! method_exists($instance, $method)) {
            return $parameters;
        }

        return $this->resolveMethodDependencies(
            $parameters, new ReflectionMethod($instance, $method)
        );
    }

    public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector)
    {
        $dependencies = $reflector->getParameters();

        try {
            $instances = $this->resolveDependencies($dependencies, $parameters);
        } catch (BindingResolutionException $e) {
            throw $e;
        }

        return $instances;
    }

    protected function resolveDependencies(array $dependencies, $parameters)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            if (array_key_exists($dependency->name, $parameters)) {
                $results[] = $parameters[$dependency->name];

                continue;
            }

            $results[] = is_null($dependency->getClass())
                            ? $this->resolvePrimitive($dependency)
                            : $this->resolveClass($dependency);
        }

        return $results;
    }

    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->unresolvablePrimitive($parameter);
    }

    protected function unresolvablePrimitive(ReflectionParameter $parameter)
    {
        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

        throw new BindingResolutionException($message);
    }

    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return app()->make($parameter->getClass()->name);
        } catch (BindingResolutionException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }
}
