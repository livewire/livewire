<?php

namespace Livewire\Factory;

use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Compiler\Compiler;
use Livewire\Finder\Finder;
use Livewire\Component;

class Factory
{
    protected $missingComponentResolvers = [];

    protected $resolvedComponentCache = [];

    public function __construct(
        protected Finder $finder,
        protected Compiler $compiler,
    ) {}

    public function create($name, $id = null): Component
    {
        [$name, $class] = $this->resolveComponentNameAndClass($name);

        $component = new $class;

        $component->setId($id ?: str()->random(20));

        $component->setName($name);

        return $component;
    }

    public function resolveMissingComponent($resolver): void
    {
        $this->missingComponentResolvers[] = $resolver;
    }

    public function resolveComponentNameAndClass($name): array
    {
        $name = $this->finder->normalizeName($name);

        $class = null;

        if (isset($this->resolvedComponentCache[$name])) {
            return [$name, $this->resolvedComponentCache[$name]];
        }

        if ($name) {
            $class = $this->finder->resolveClassComponentClassName($name);

            if (! $class) {
                $path = $this->finder->resolveMultiFileComponentPath($name);

                if (! $path) {
                    $path = $this->finder->resolveSingleFileComponentPath($name);
                }

                if ($path) {
                    $class = $this->compiler->compile($path);
                }
            }
        }

        if (! $class || ! class_exists($class) || ! is_subclass_of($class, Component::class)) {
            foreach ($this->missingComponentResolvers as $resolver) {
                if ($class = $resolver($name)) {
                    $this->finder->addComponent($name, $class);

                    break;
                }
            }
        }

        if (! $class || ! class_exists($class) || ! is_subclass_of($class, Component::class)) {
            throw new ComponentNotFoundException(
                "Unable to find component: [{$name}]"
            );
        }

        $this->resolvedComponentCache[$name] = $class;

        return [$name, $class];
    }

    public function resolveComponentClass($name): string
    {
        [$name, $class] = $this->resolveComponentNameAndClass($name);

        return $class;
    }

    public function resolveComponentName($name): string
    {
        $component = $this->create($name);

        return $component->getName();
    }

    public function exists($name): bool
    {
        try {
            $this->create($name);

            return true;
        } catch (ComponentNotFoundException $e) {
            return false;
        }
    }
}
