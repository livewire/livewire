<?php

namespace Livewire\Factory;

use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Compiler\Compiler;
use Livewire\Finder\Finder;
use Livewire\Component;

class Factory
{
    public function __construct(
        protected Finder $finder,
        protected Compiler $compiler,
    ) {}

    public function create($name, $id = null)
    {
        if (is_subclass_of($name, Component::class)) {
            $name = is_object($name) ? get_class($name) : $name;
        }

        $name = $this->finder->normalizeName($name);

        if ($name) {
            $class = $this->finder->resolveClassName($name);

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
            throw new ComponentNotFoundException(
                "Unable to find component: [{$name}]"
            );
        }

        $component = new $class;

        $component->setId($id ?: str()->random(20));

        $component->setName($name);

        return $component;
    }
}
