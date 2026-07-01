<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\ComponentHook;

class SupportAttributes extends ComponentHook
{
    function boot(...$params)
    {
        $callback = function ($attribute) use ($params) {
            if (method_exists($attribute, 'boot')) {
                $attribute->boot(...$params);
            }
        };

        $this->getLivewireAttributes()->each($callback);
    }

    function mount(...$params)
    {
        $callback = function ($attribute) use ($params) {
            if (method_exists($attribute, 'mount')) {
                $attribute->mount(...$params);
            }
        };

        $this->getLivewireAttributes()->each($callback);
    }

    function hydrate(...$params)
    {
        $callback = function ($attribute) use ($params) {
            if (method_exists($attribute, 'hydrate')) {
                $attribute->hydrate(...$params);
            }
        };

        $this->getLivewireAttributes()->each($callback);
    }

    function update($propertyName, $fullPath, $newValue)
    {
        $callbacks = $this->getLivewireAttributes()
            ->filter(fn ($attr) => $attr->getLevel() === AttributeLevel::PROPERTY)
            // Call "update" on the root property attribute even if it's a deep update...
            ->filter(fn ($attr) => str($fullPath)->startsWith($attr->getName() . '.') || $fullPath === $attr->getName())
            ->map(function ($attribute) use ($fullPath, $newValue) {
                if (method_exists($attribute, 'update')) {
                    return $attribute->update($fullPath, $newValue);
                }
            });

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function call($method, $params, $returnEarly)
    {
        $callbacks = $this->getLivewireAttributes()
            ->filter(fn ($attr) => $attr->getLevel() === AttributeLevel::METHOD)
            ->filter(fn ($attr) => $attr->getName() === $method)
            ->map(function ($attribute) use ($params, $returnEarly) {
                if (method_exists($attribute, 'call')) {
                    return $attribute->call($params, $returnEarly);
                }
            });

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function render(...$params)
    {
        $callbacks = $this->getLivewireAttributes()
            ->map(function ($attribute) use ($params) {
                if (method_exists($attribute, 'render')) {
                    return $attribute->render(...$params);
                }
            });

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $callback(...$params);
                }
            }
        };
    }

    function dehydrate(...$params)
    {
        $callback = function ($attribute) use ($params) {
            if (method_exists($attribute, 'dehydrate')) {
                $attribute->dehydrate(...$params);
            }
        };

        $this->getLivewireAttributes()->each($callback);
    }

    function destroy(...$params)
    {
        $callback = function ($attribute) use ($params) {
            if (method_exists($attribute, 'destroy')) {
                $attribute->destroy(...$params);
            }
        };

        $this->getLivewireAttributes()->each($callback);
    }

    function exception(...$params)
    {
        $callback = function ($attribute) use ($params) {
            if (method_exists($attribute, 'exception')) {
                $attribute->exception(...$params);
            }
        };

        $this->getLivewireAttributes()->each($callback);
    }

    protected function getLivewireAttributes()
    {
        return $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class);
    }
}
