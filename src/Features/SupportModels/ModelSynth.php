<?php

namespace Livewire\Features\SupportModels;

use stdClass;
use Synthetic\SyntheticValidation;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;
use Livewire\Drawer\Utils;
use Illuminate\Support\Stringable;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Database\ModelIdentifier;
use Exception;

class ModelSynth extends Synth {
    use SerializesAndRestoresModelIdentifiers;

    public static $key = 'mdl';

    static function match($target) {
        return $target instanceof Model;
    }

    function dehydrate($target, $context) {
        if (! $target->exists) {
            throw new \Exception('Can\'t set model as property if it hasn\'t been persisted yet');
        } else {
            $serializedModel = (array) $this->getSerializedPropertyValue($target);

            $context->addMeta('class', $serializedModel['class']);
            $context->addMeta('key', $serializedModel['id']);
        }
    }

    function hydrate($data, $meta) {
        $key = $meta['key'];
        $class = $meta['class'];

        $model = (new $class)->newQueryForRestoration($key)->useWritePdo()->firstOrFail();

        return $model;
    }

    function get(&$target, $key) {
        throw new \Exception('Can\'t access model properties directly');
    }

    function set(&$target, $key, $value, $pathThusFar, $fullPath, $root) {
        throw new \Exception('Can\'t set model properties directly');
    }

    function call($target, $method, $params, $addEffect) {
        throw new \Exception('Can\'t call model methods directly');
    }
}
