<?php

namespace Livewire\HydrationMiddleware;

use Livewire\TemporarilyUploadedFile;

class HydrateFileUploadsAsPublicProperties implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $publicProperties = $unHydratedInstance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if (! is_string($value)) continue;

            if (TemporarilyUploadedFile::canUnserialize($value)) {
                $unHydratedInstance->$property = TemporarilyUploadedFile::unserializeFromLivewireRequest($value);
            }
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicProperties = $instance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if ($value instanceof TemporarilyUploadedFile) {
                $instance->$property = $value->serializeForLivewireResponse();
            } elseif (is_array($value) && isset($value[0]) && $value[0] instanceof TemporarilyUploadedFile) {
                $instance->$property = $value[0]::serializeMultipleForLivewireResponse($value);
            }
        }
    }
}
