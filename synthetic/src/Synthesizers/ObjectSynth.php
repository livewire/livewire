<?php

namespace Synthetic\Synthesizers;

use Synthetic\Component;
use Synthetic\Utils;

class ObjectSynth extends Synth {
    public static $key = 'obj';

    static function match($target) {
        return is_object($target);
    }

    function dehydrate($target, $context) {
        $this->ensureSynthetic($target);

        $properties = Utils::getPublicPropertiesDefinedOnSubclass($target);

        $context->addMeta('class', $this->getClass($target));

        return $properties;
    }

    public function getClass($target)
    {
        return get_class($target);
    }

    function hydrate($value, $meta) {
        $class = $meta['class'];
        $target = new $class;
        $properties = $value;

        foreach ($properties as $key => $value) {
            $target->$key = $value;
        }

        return $target;
    }

    function &get($target, $key) {
        return $target->{$key};
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
        return array_values(array_diff($methods, $jsMethods, ['render']));
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
        return $target->{$method}(...$params);
    }

    function ensureSynthetic($target) {
        abort_unless(
            $target instanceof Component,
            419,
            'You can only synthesize a class that implements the Synthetic interface.'
        );
    }
}
