<?php

namespace Livewire;

use function Synthetic\wrap;

use Livewire\Mechanisms\ComponentRegistry;
use Synthetic\Utils;
use Synthetic\Synthesizers\ObjectSynth;

use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\DataStore;

class LivewireSynth extends ObjectSynth
{
    public static $key = 'lw';

    public static $renderContexts = [];

    static function match($target) {
        return $target instanceof \Livewire\Component;
    }

    function dehydrate($target, $context) {
        $context->addMeta('id', $target->getId());
        $context->addMeta('name', $target->getName());

        return Utils::getPublicPropertiesDefinedOnSubclass($target);
    }

    function hydrate($value, $meta) {
        ['name' => $name, 'id' => $id] = $meta;

        return app(ComponentRegistry::class)->new($name, $value, $id);
    }

    function set(&$target, $key, $value) {
        parent::set($target, $key, $value);
    }

    function unset(&$target, $key, $value) {
        parent::set($target, $key, $value);
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
