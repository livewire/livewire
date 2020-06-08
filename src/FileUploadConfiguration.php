<?php

namespace Livewire;

use Illuminate\Support\Facades\Storage;

class FileUploadConfiguration
{
    public static function storage()
    {
        if (app()->environment('testing')) {
            // We want to "fake" the first time in a test run, but not again because
            // ::fake() whipes the storage directory every time its called.
            rescue(function () {
                // If the storage disk is not found (meaning it's the first time),
                // this will throw an error and trip the second callback.
                return Storage::disk(static::disk());
            }, function () {
                return Storage::fake(static::disk());
            });
        }

        return Storage::disk(static::disk());
    }

    public static function disk()
    {
        return app()->environment('testing')
            ? 'tmp-for-tests'
            : (config('livewire.temporary_file_upload.disk') ?: config('filesystems.default'));
    }

    public static function diskConfig()
    {
        return config('filesystems.disks.'.static::disk());
    }

    public static function isUsingS3()
    {
        $diskBeforeTestFake = config('livewire.temporary_file_upload.disk') ?: config('filsystems.default');

        return config('filesystems.disks.'.strtolower($diskBeforeTestFake).'.driver') === 's3';
    }

    public static function directory()
    {
        return config('livewire.temporary_file_upload.directory') ?: 'livewire-tmp/';
    }

    public static function middleware()
    {
        return config('livewire.temporary_file_upload.middleware') ?: 'throttle:60,1';
    }

    public static function rules()
    {
        $rules = config('livewire.temporary_file_upload.rules');

        if (is_null($rules)) return ['required', 'file', 'max:12288'];

        if (is_array($rules)) return $rules;

        return explode('|', $rules);
    }
}
