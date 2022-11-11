<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

use Synthetic\Component;
use Livewire\Drawer\Utils;

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

    function set(&$target, $key, $value) {
        $target->$key = $value;
    }

    function ensureSynthetic($target) {
        abort_unless(
            $target instanceof Component,
            419,
            'You can only synthesize a class that implements the Synthetic interface.'
        );
    }
}
