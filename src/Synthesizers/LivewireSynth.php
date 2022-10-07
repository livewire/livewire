<?php

namespace Livewire\Synthesizers;

use Synthetic\Utils;
use Synthetic\Synthesizers\ObjectSynth;
use Livewire\Mechanisms\RenderComponent;

use function Synthetic\wrap;

class LivewireSynth extends ObjectSynth
{
    public static $key = 'lw';

    public static $renderContexts = [];

    static function match($target) {
        return $target instanceof \Livewire\Component;
    }

    function dehydrate($target, $context) {
        $properties = Utils::getPublicPropertiesDefinedOnSubclass($target);

        $rendered = wrap($target)->render();

        $html = app('livewire')->renderBladeView($target, $rendered, $properties);

        $context->addMeta('children', $target->getChildren());
        $context->addMeta('id', $target->getId());

        $context->addEffect('html', $html);

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
            $target->$key = $value;
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
