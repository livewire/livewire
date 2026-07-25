<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\ComponentHook;

class SupportAttributes extends ComponentHook
{
    function boot(...$params)
    {
        $this->runSetupPhase('boot', $params);
    }

    function mount(...$params)
    {
        $this->runSetupPhase('mount', $params);
    }

    function hydrate(...$params)
    {
        $this->runSetupPhase('hydrate', $params);
    }

    // The phases that wire an attribute up (as opposed to render/dehydrate,
    // which report on it). Marking the phase before running it means an
    // attribute registered from inside one of these hooks — or any time
    // later — is caught up by mergeOutsideAttributes() exactly once...
    protected function runSetupPhase($phase, $params)
    {
        $attributes = $this->getLivewireAttributes();

        $this->component->markAttributePhaseAsElapsed($phase, $params);

        $attributes->each(function ($attribute) use ($phase, $params) {
            if (method_exists($attribute, $phase)) {
                $attribute->{$phase}(...$params);
            }
        });
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
        $this->getLivewireAttributes()->each(function ($attribute) use ($params) {
            if (method_exists($attribute, 'dehydrate')) {
                $attribute->dehydrate(...$params);
            }
        });
    }

    function destroy(...$params)
    {
        $this->getLivewireAttributes()->each(function ($attribute) use ($params) {
            if (method_exists($attribute, 'destroy')) {
                $attribute->destroy(...$params);
            }
        });
    }

    function exception(...$params)
    {
        $this->getLivewireAttributes()->each(function ($attribute) use ($params) {
            if (method_exists($attribute, 'exception')) {
                $attribute->exception(...$params);
            }
        });
    }

    protected function getLivewireAttributes()
    {
        return $this->component
            ->getAttributes()
            ->whereInstanceOf(LivewireAttribute::class);
    }
}
