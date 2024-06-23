<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\ComponentHook;

class SupportAttributes extends ComponentHook
{
    function boot(...$params)
    {
        $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
            ->each(function ($attribute) use ($params) {
                if (method_exists($attribute, 'boot')) {
                    $attribute->boot(...$params);
                }
            });
    }

    function mount(...$params)
    {
        $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
            ->each(function ($attribute) use ($params) {
                if (method_exists($attribute, 'mount')) {
                    $attribute->mount(...$params);
                }
            });
    }

    function hydrate(...$params)
    {
        $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
            ->each(function ($attribute) use ($params) {
                if (method_exists($attribute, 'hydrate')) {
                    $attribute->hydrate(...$params);
                }
            });
    }

    function update($propertyName, $fullPath, $newValue)
    {
        $callbacks = $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
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
        $callbacks = $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
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
        $callbacks = $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
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
        $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
            ->each(function ($attribute) use ($params) {
                if (method_exists($attribute, 'dehydrate')) {
                    $attribute->dehydrate(...$params);
                }
            });
    }

    function destroy(...$params)
    {
        $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
            ->each(function ($attribute) use ($params) {
                if (method_exists($attribute, 'destroy')) {
                    $attribute->destroy(...$params);
                }
            });
    }

    function exception(...$params)
    {
        $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class)
            ->each(function ($attribute) use ($params) {
                if (method_exists($attribute, 'exception')) {
                    $attribute->exception(...$params);
                }
            });
    }

}
