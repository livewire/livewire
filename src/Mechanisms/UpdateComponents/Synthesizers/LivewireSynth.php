<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

use function Livewire\wrap;
use function Livewire\trigger;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;

use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportModels\Lazy;

class LivewireSynth extends Synth
{
    public static $key = 'lw';

    public static $renderContexts = [];

    static function match($target) {
        return $target instanceof \Livewire\Component;
    }

    function dehydrate($target, $context, $dehydrateChild) {
        trigger('dehydrate', $target, $context);

        $context->addMeta('id', $target->getId());
        $context->addMeta('name', $target->getName());

        $data = Utils::getPublicPropertiesDefinedOnSubclass($target);

        foreach ($data as $key => $value) {
            $data[$key] = $dehydrateChild($value, ['parent' => $target, 'key' => $key]);
        }

        return $data;
    }

    function hydrate($data, $meta, $hydrateChild) {
        ['name' => $name, 'id' => $id] = $meta;

        $component = app(ComponentRegistry::class)->new($name, [], $id);

        foreach ($data as $key => $rawChild) {
            if (! property_exists($component, $key)) continue;

            $child = $hydrateChild($rawChild);

            // Typed properties shouldn't be set back to "null". It will throw an error...
            if ((new \ReflectionProperty($component, $key))->getType() && is_null($child)) continue;

            $component->$key = $child;
        }

        trigger('hydrate', $component, $meta);

        return $component;
    }

    function set(&$target, $key, $value) {
        $target->$key = $value;
    }

    function methods($target)
    {
        $methods = Utils::getPublicMethodsDefinedBySubClass($target);

        // @todo: move this elsewhere...
        // Remove JS methods from method list:
        $jsMethods = $this->getJsMethods($target);

        // Also remove "render" from the list...
        $methods =  array_values(array_diff($methods, $jsMethods, ['render']));

        $addMethod = function ($name) use (&$methods) {
            array_push($methods, $name);
        };

        trigger('methods', $target, $addMethod);

        return array_diff($methods, ['render']);
    }

    function getJsMethods($target)
    {
        $methods = (new \ReflectionClass($target))->getMethods(\ReflectionMethod::IS_PUBLIC);

        return collect($methods)
            ->filter(function ($subject) {
                return $subject->getDocComment() && str($subject->getDocComment())->contains('@js');
            })
            ->map(function ($subject) use ($target) {
                return $subject->getName();
            })
            ->toArray();
    }

    function call($target, $method, $params, $addEffect) {
        return wrap($target)->{$method}(...$params);
    }
}
