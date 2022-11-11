<?php

namespace Livewire\Features\SupportFileUploads;

use Synthetic\SyntheticValidation;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\ModelIdentifier;
use Exception;

class FileUploadSynth extends Synth {
    public static $key = 'fil';

    static function match($target) {
        // return $target instanceof TemporaryUploadedFile;
        return $target instanceof UploadedFile;
    }

    function dehydrate($target, $context) {
        $value = $this->dehydratePropertyFromWithFileUploads($target);

        return $value;
        // if ($newValue !== $value) {
        //     $target->{$property} = $newValue;
        // }
    }

    public function dehydratePropertyFromWithFileUploads($value)
    {
        if (TemporaryUploadedFile::canUnserialize($value)) {
            return TemporaryUploadedFile::unserializeFromLivewireRequest($value);
        }

        if ($value instanceof TemporaryUploadedFile) {
            return  $value->serializeForLivewireResponse();
        }

        if (is_array($value) && isset(array_values($value)[0])) {
            $isValid = true;

            foreach ($value as $key => $arrayValue) {
                if (!($arrayValue instanceof TemporaryUploadedFile) || !is_numeric($key)) {
                    $isValid = false;
                    break;
                }
            }

            if ($isValid) {
                return array_values($value)[0]::serializeMultipleForLivewireResponse($value);
            }
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->dehydratePropertyFromWithFileUploads($item);
            }
        }

        if ($value instanceof Wireable) {
            $keys = array_keys((array) get_object_vars($value));

            foreach ($keys as $key) {
                $value->{$key} = $this->dehydratePropertyFromWithFileUploads($value->{$key});
            }
        }

        return $value;
    }

    function hydrate($value, $meta) {
        if (TemporaryUploadedFile::canUnserialize($value)) {
            return TemporaryUploadedFile::unserializeFromLivewireRequest($value);
        }
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
