<?php

namespace Livewire\HydrationMiddleware;

use finfo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\LivewireUploadedFile;

class HydrateFileUploadsAsPublicProperties implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $publicProperties = $unHydratedInstance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if (Str::startsWith($value, 'livewire-file:')) {
                $filename = Str::after($value, 'livewire-file:');
                $file_path = Storage::path('livewire/'.$filename);
                $finfo = new finfo(FILEINFO_MIME_TYPE);

                if (Storage::exists('livewire/'.$filename)) {
                    $unHydratedInstance->$property = new LivewireUploadedFile(
                        $file_path,
                        $filename,
                        $finfo->file($file_path),
                        filesize($file_path),
                        0,
                        false
                    );
                }
            }

        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicProperties = $instance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if ($value instanceof LivewireUploadedFile) {
                $instance->$property = 'livewire-file:'.$value->getFilename();
            }
        }
    }
}
