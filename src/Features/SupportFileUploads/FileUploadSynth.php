<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Illuminate\Http\UploadedFile;

class FileUploadSynth extends Synth {
    public static $key = 'fil';

    static function match($target) {
        return $target instanceof UploadedFile;
    }

    function dehydrate($target) {
        return [
            $this->dehydratePropertyFromWithFileUploads($target),
            $target instanceof TemporaryUploadedFile ? static::metaFor($target) : [],
        ];
    }

    public static function metaFor(TemporaryUploadedFile $file)
    {
        $meta = [];

        try {
            $name = $file->getClientOriginalName();

            // Names extracted from hashed file paths can decode into binary
            // garbage that would corrupt the snapshot's JSON encoding...
            if (is_string($name) && mb_check_encoding($name, 'UTF-8')) {
                $meta['name'] = $name;
            }
        } catch (\Throwable $e) {
            //
        }

        try {
            $meta['previewUrl'] = $file->temporaryUrl();
        } catch (\Throwable $e) {
            // Not previewable, or the disk doesn't support temporary URLs...
        }

        return $meta;
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

        if ($value instanceof \Livewire\Wireable) {
            $keys = array_keys(get_object_vars($value));

            foreach ($keys as $key) {
                $value->{$key} = $this->dehydratePropertyFromWithFileUploads($value->{$key});
            }
        }

        return $value;
    }

    function hydrate($value) {
        if (TemporaryUploadedFile::canUnserialize($value)) {
            return TemporaryUploadedFile::unserializeFromLivewireRequest($value);
        }
    }
}
