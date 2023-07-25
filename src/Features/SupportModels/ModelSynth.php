<?php

namespace Livewire\Features\SupportModels;

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
        if (! $target->exists) {
            throw new \Exception('Can\'t set model as property if it hasn\'t been persisted yet');
        }

        $serializedModel = (array) $this->getSerializedPropertyValue($target);

        return [
            null,
            ['class' => $serializedModel['class'], 'key' => $serializedModel['id']],
        ];
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

    function set(&$target, $key, $value, $pathThusFar, $fullPath) {
        throw new \Exception('Can\'t set model properties directly');
    }

    function call($target, $method, $params, $addEffect) {
        throw new \Exception('Can\'t call model methods directly');
    }
}
