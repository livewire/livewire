<?php

namespace Livewire\Synthesizers;

use Synthetic\SyntheticValidation;
use Synthetic\Synthesizers\Synth;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\ModelIdentifier;
use Exception;

class EloquentModelSynth extends Synth {
    use SerializesAndRestoresModelIdentifiers, SyntheticValidation;

    public static $key = 'mdl';

    static function match($target) {
        return $target instanceof Model;
    }

    function dehydrate($target, $context) {
        $value = $this->getSerializedPropertyValue($target);

        $attributes = $target->getAttributes();

        foreach ($value->relations as $relation) {
            $attributes[$relation] = $target->getRelationValue($relation);
        }

        $context->addMeta('connection', $value->connection);
        $context->addMeta('relations', $value->relations);
        $context->addMeta('class', $value->class);
        $context->addMeta('key', $value->id);

        return $attributes;
    }

    function hydrate($value, $meta) {
        if ($meta['key'] === null) {
            return new $meta['class']($value);
        }

        $identifier = new ModelIdentifier(
            $meta['class'],
            $meta['key'],
            $meta['relations'],
            $meta['connection'],
        );

        return $this->getRestoredPropertyValue($identifier);
    }

    function &get($target, $key) {
        $target->getAttribute($key);
    }

    function set(&$target, $key, $value) {
        $target->setAttribute($key, $value);
    }

    function methods($target)
    {
        return ['save'];
    }

    function call($target, $method, $params, $addEffect) {
        if ($method === 'save') {
            $models = $this->validate(
                $target->getAttributes(),
                $target->rules(),
            );

            return $target->save();
        }

        throw new Exception;
    }
}
