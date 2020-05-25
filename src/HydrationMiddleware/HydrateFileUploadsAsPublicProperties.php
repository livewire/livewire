<?php

namespace Livewire\HydrationMiddleware;

use finfo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\LivewireNotYetUploadedFile;
use Livewire\LivewireUploadedFile;

class HydrateFileUploadsAsPublicProperties implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $publicProperties = $unHydratedInstance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if (! is_string($value)) continue;

            if (LivewireUploadedFile::canUnserialize($value)) {
                $unHydratedInstance->$property = LivewireUploadedFile::unserializeFromLivewireRequest($value);
            } elseif (LivewireNotYetUploadedFile::canUnserialize($value)) {
                $unHydratedInstance->$property = LivewireUploadedFile::unserializeFromLivewireRequest($value);
            }
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicProperties = $instance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if (static::isLivewireUploadedFile($value)) {
                $instance->$property = $value->serializeForLivewireResponse();
            } elseif (is_array($value) && isset($value[0]) && static::isLivewireUploadedFile($value[0])) {
                $instance->$property = $value[0]::serializeMultipleForLivewireResponse($value);
            }
        }
    }

    protected static function isLivewireUploadedFile($value)
    {
        return $value instanceof LivewireNotYetUploadedFile
            || $value instanceof LivewireUploadedFile;
    }
}
