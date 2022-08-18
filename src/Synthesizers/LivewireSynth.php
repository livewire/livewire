<?php

namespace Livewire\Synthesizers;

use Synthetic\Utils;
use Synthetic\Synthesizers\ObjectSynth;
use Livewire\Mechanisms\RenderComponent;

class LivewireSynth extends ObjectSynth
{
    public static $key = 'lw';

    public static $renderContexts = [];

    static function match($target) {
        return $target instanceof \Livewire\Component;
    }

    function dehydrate($target, $context) {
        $properties = Utils::getPublicPropertiesDefinedOnSubclass($target);

        $html = RenderComponent::renderComponentBladeView($target, $target->render(), $properties);

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
        $target->setChildren($children);
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

        return array_diff($methods, ['render']);
    }

    function call($target, $method, $params, $addEffect) {
        return parent::call($target, $method, $params, $addEffect);
    }
}
