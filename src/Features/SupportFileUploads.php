<?php

namespace Livewire\Features;

use Livewire\Livewire;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\Wireable;

class SupportFileUploads
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('property.hydrate', function ($property, $value, $component, $request) {
            $uses = array_flip(class_uses_recursive($component));

            if (! in_array(WithFileUploads::class, $uses)) return;

            if (TemporaryUploadedFile::canUnserialize($value)) {
                $component->{$property} = TemporaryUploadedFile::unserializeFromLivewireRequest($value);
            }
        });

        Livewire::listen('property.dehydrate', function ($property, $value, $component, $response) {
            $uses = array_flip(class_uses_recursive($component));

            if (! in_array(WithFileUploads::class, $uses)) return;

            $newValue = $this->dehydratePropertyFromWithFileUploads($value);

            if ($newValue !== $value) {
                $component->{$property} = $newValue;
            }
        });
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
}
