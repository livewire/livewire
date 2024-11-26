<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\WhitespacePathNormalizer;

class FileUploadConfiguration
{
    public static function storage()
    {
        $disk = static::disk();

        if (app()->runningUnitTests()) {
            // We want to "fake" the first time in a test run, but not again because
            // Storage::fake() wipes the storage directory every time its called.
            rescue(
                // If the storage disk is not found (meaning it's the first time),
                // this will throw an error and trip the second callback.
                fn() => Storage::disk($disk),
                fn() => Storage::fake($disk),
                // swallows the error that is thrown on the first try
                report: false
            );
        }

        return Storage::disk($disk);
    }

    public static function disk()
    {
        if (app()->runningUnitTests()) {
            return 'tmp-for-tests';
        }

        return config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
    }

    public static function diskConfig()
    {
        return config('filesystems.disks.'.static::disk());
    }

    public static function isUsingS3()
    {
        $diskBeforeTestFake = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');

        return config('filesystems.disks.'.strtolower($diskBeforeTestFake).'.driver') === 's3';
    }

    public static function isUsingGCS()
    {
        $diskBeforeTestFake = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');

        return config('filesystems.disks.'.strtolower($diskBeforeTestFake).'.driver') === 'gcs';
    }

    public static function normalizeRelativePath($path)
    {
        return (new WhitespacePathNormalizer)->normalizePath($path);
    }

    public static function directory()
    {
        return static::normalizeRelativePath(config('livewire.temporary_file_upload.directory') ?: 'livewire-tmp');
    }

    protected static function s3Root()
    {
        if (! static::isUsingS3()) return '';

        $diskConfig = static::diskConfig();

        if (! is_array($diskConfig)) return '';

        $root = $diskConfig['root'] ?? null;

        return $root !== null ? static::normalizeRelativePath($root) : '';
    }

    public static function path($path = '', $withS3Root = true)
    {
        $prefix = $withS3Root ? static::s3Root() : '';
        $directory = static::directory();
        $path = static::normalizeRelativePath($path);

        return $prefix.($prefix ? '/' : '').$directory.($path ? '/' : '').$path;
    }

    public static function mimeType($filename)
    {
        $mimeType = static::storage()->mimeType(static::path($filename));

        return $mimeType === 'image/svg' ? 'image/svg+xml' : $mimeType;
    }

    public static function lastModified($filename)
    {
        return static::storage()->lastModified($filename);
    }

    public static function middleware()
    {
        return config('livewire.temporary_file_upload.middleware') ?: 'throttle:60,1';
    }

    public static function shouldCleanupOldUploads()
    {
        return config('livewire.temporary_file_upload.cleanup', true);
    }

    public static function rules()
    {
        $rules = config('livewire.temporary_file_upload.rules');

        if (is_null($rules)) return ['required', 'file', 'max:12288'];

        if (is_array($rules)) return $rules;

        return explode('|', $rules);
    }

    public static function maxUploadTime()
    {
        return config('livewire.temporary_file_upload.max_upload_time') ?: 5;
    }
}
