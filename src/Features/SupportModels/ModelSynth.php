<?php

namespace Livewire\Features\SupportModels;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ClassMorphViolationException;

class ModelSynth extends Synth {
    use SerializesAndRestoresModelIdentifiers, IsLazy;

    public static $key = 'mdl';

    static function match($target) {
        return $target instanceof Model;
    }

    function dehydrate($target) {
        if ($this->isLazy($target)) {
            $meta = $this->getLazyMeta($target);

            return [
                null,
                $meta,
            ];
        }

        $class = $target::class;

        try {
            // If no alias is found, this just returns the class name
            $alias = $target->getMorphClass();
        } catch (ClassMorphViolationException $e) {
            // If the model is not using morph classes, this exception is thrown
            $alias = $class;
        }

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
        $class = $meta['class'] ?? null;

        // If no alias found, this returns `null`
        $aliasClass = Relation::getMorphedModel($class);

        if (! is_null($aliasClass)) {
            $class = $aliasClass;
        }

        // Verify class extends Model even though checksum protects this...
        if (! $class || ! is_a($class, Model::class, true)) {
            throw new \Exception('Livewire: Invalid model class.');
        }

        // If no key is provided then an empty model is returned
        if (! array_key_exists('key', $meta)) {
            return new $class;
        }

        $key = $meta['key'];

        return $this->makeLazyProxy($class, $meta, function () use ($class, $key) {
            return (new $class)->newQueryForRestoration($key)->useWritePdo()->firstOrFail();
        });
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
