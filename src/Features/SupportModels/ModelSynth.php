<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Model;

class ModelSynth extends Synth {
    use SerializesAndRestoresModelIdentifiers;

    public static $key = 'mdl';

    static function match($target) {
        return $target instanceof Model;
    }

    function dehydrate($target) {
        // If no alias is found, this just returns the class name
        $alias = $target->getMorphClass();

        $serializedModel = $target->exists
            ? (array) $this->getSerializedPropertyValue($target)
            : null;

        $meta = ['class' => $alias];

        // If the model doesn't exist as it's an empty model or has been
        // recently deleted, then we don't want to include any key.
        if ($serializedModel) $meta['key'] = $serializedModel['id'];
        

        return [
            null,
            $meta,
        ];
    }

    function hydrate($data, $meta) {
        $class = $meta['class'];

        // If no alias found, this returns `null`
        $aliasClass = Relation::getMorphedModel($class);

        if (! is_null($aliasClass)) {
            $class = $aliasClass;
        }

        // If no key is provided then an empty model is returned
        if (! array_key_exists('key', $meta)) {
            return new $class;
        }

        $key = $meta['key'];

        $model = (new $class)->newQueryForRestoration($key)->useWritePdo()->firstOrFail();

        return $model;
    }

    function get(&$target, $key) {
        throw new \Exception('Can\'t access model properties directly');
    }

    function set(&$target, $key, $value, $pathThusFar, $fullPath) {
        throw new \Exception('Can\'t set model properties directly');
    }

    function call($target, $method, $params, $addEffect) {
        throw new \Exception('Can\'t call model methods directly');
    }
}
