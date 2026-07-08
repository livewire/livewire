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
        $meta = [];

        // Carry the generated preview URL through the snapshot so the next
        // request serves the same URL instead of signing a new one — a changed
        // src would make the browser re-download the whole file...
        if ($target instanceof TemporaryUploadedFile && ($cached = $target->getCachedTemporaryUrl())) {
            $meta['url'] = $cached['url'];
            $meta['exp'] = $cached['exp'];
        }

        return [$this->dehydratePropertyFromWithFileUploads($target), $meta];
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

    function hydrate($value, $meta) {
        if (TemporaryUploadedFile::canUnserialize($value)) {
            $file = TemporaryUploadedFile::unserializeFromLivewireRequest($value);

            if ($file instanceof TemporaryUploadedFile && isset($meta['url'], $meta['exp'])) {
                $file->setCachedTemporaryUrl($meta['url'], $meta['exp']);
            }

            return $file;
        }
    }
}
