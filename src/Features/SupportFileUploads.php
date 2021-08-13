<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

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

        if (is_array($value) && isset(array_values($value)[0]) && array_values($value)[0] instanceof TemporaryUploadedFile && is_numeric(key($value))) {
            return array_values($value)[0]::serializeMultipleForLivewireResponse($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->dehydratePropertyFromWithFileUploads($item);
            }
        }

        return $value;
    }
}
