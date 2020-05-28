<?php

namespace Livewire;

use Illuminate\Support\Facades\URL;

class GenerateSignedUploadUrlFake
{
    public static function forLocal()
    {
        return URL::temporarySignedRoute(
            'livewire.upload-file', now()->addMinutes(5)
        );
    }

    public static function forS3($file, $visibility = 'private')
    {
    }
}
