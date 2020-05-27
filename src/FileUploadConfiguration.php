<?php

namespace Livewire;

use Illuminate\Support\Facades\Storage;

class FileUploadConfiguration
{
    public static function storage()
    {
        if (app()->environment('testing')) {
            static $cache;
            return $cache ?: $cache = Storage::fake(static::disk());
        }

        return Storage::disk(static::disk());
    }

    public static function disk()
    {
        return app()->environment('testing')
            ? 'tmp-for-tests'
            : (config('livewire.temporary_file_upload.disk') ?: config('filsystems.default'));
    }

    public static function middleware()
    {
        return config('livewire.temporary_file_upload.middleware') ?: 'throttle:60,1';
    }

    public static function rules()
    {
        return config('livewire.temporary_file_upload.rules') ?: 'file|max:12288';
    }
}
