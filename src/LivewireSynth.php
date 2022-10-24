<?php

namespace Livewire;

use function Synthetic\wrap;
use Synthetic\Utils;
use Synthetic\Synthesizers\ObjectSynth;

use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\ComponentDataStore;

class LivewireSynth extends ObjectSynth
{
    public static $key = 'lw';

    public static $renderContexts = [];

    static function match($target) {
        return $target instanceof \Livewire\Component;
    }

    function dehydrate($target, $context) {
        $properties = Utils::getPublicPropertiesDefinedOnSubclass($target);

        if (! ComponentDataStore::get($target, 'skipRender', false)) {
            $rendered = method_exists($target, 'render')
                ? wrap($target)->render()
                : view("livewire.{$target::getName()}");

            $html = app('livewire')->renderBladeView($target, $rendered, $properties);

            $context->addEffect('html', $html);
        }

        $context->addMeta('children', $target->getChildren());
        $context->addMeta('id', $target->getId());


        return parent::dehydrate($target, $context);
    }

    function hydrate($value, $meta) {
        [
            'children' => $children,
            'class' => $class,
            'id' => $id,
        ] = $meta;

        $target = new $class;
        $target->setPreviouslyRenderedChildren($children);
        $target->setId($id);

        $properties = $value;

        foreach ($properties as $key => $value) {
            // Typed properties shouldn't be set back to "null". It will throw an error...
            if (property_exists($target, $key) && (new \ReflectionProperty($target, $key))->getType()){
                is_null($value) || $target->$key = $value;
            } else {
                $target->$key = $value;
            }
        }

        return $target;
    }

    function &get($target, $key) {
        return parent::get($target, $key);
    }

    function set(&$target, $key, $value) {
        return parent::set($target, $key, $value);
    }

    function unset(&$target, $key, $value) {
        return parent::set($target, $key, $value);
    }

    function methods($target)
    {
        $methods = parent::methods($target);

        $addMethod = function ($name) use (&$methods) {
            array_push($methods, $name);
        };

        app('synthetic')->trigger('methods', $target, $addMethod);

        return array_diff($methods, ['render']);
    }

    function call($target, $method, $params, $addEffect) {
        return wrap($target)->{$method}(...$params);
    }
}
