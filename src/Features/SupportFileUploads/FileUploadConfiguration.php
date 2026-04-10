<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\WhitespacePathNormalizer;

use function Livewire\invade;

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

    public static function chunkSize()
    {
        // Default to exactly 1 MiB to match nginx's compiled-in default
        // `client_max_body_size`. Both Forge and Laravel Cloud use the
        // nginx default, so a larger chunk would 413 out of the box.
        return config('livewire.temporary_file_upload.chunk.size', 1024 * 1024);
    }

    public static function isChunkingEnabled()
    {
        return (bool) config('livewire.temporary_file_upload.chunk.enabled', false);
    }

    public static function chunkRetryDelays()
    {
        return config('livewire.temporary_file_upload.chunk.retry_delays', [500, 1000, 3000]);
    }

    public static function chunkMaxUploadTime()
    {
        // 24 hours by default — aligns with Livewire's existing 24-hour
        // temporary-upload cleanup window, and comfortably handles realistic
        // multi-GB uploads on slow connections (e.g. 10GB at 5Mbps ≈ 4.5h).
        return config('livewire.temporary_file_upload.chunk.max_upload_time', 60 * 24);
    }

    public static function chunkMiddleware()
    {
        // Default is looser than the legacy single-request upload throttle
        // (which is 60/minute) because chunked uploads inherently make many
        // small requests for a single large file. Sites that expose uploads
        // to anonymous visitors should tighten this — see uploads.md.
        return config('livewire.temporary_file_upload.chunk.middleware') ?: 'throttle:600,1';
    }

    /**
     * The absolute maximum upload size in bytes for chunked uploads when no
     * `max:` rule is configured. Acts as a hard ceiling so a missing rule
     * can never become an unbounded upload claim.
     */
    public static function chunkAbsoluteMaxBytes()
    {
        return config('livewire.temporary_file_upload.chunk.absolute_max_bytes', 5 * 1024 * 1024 * 1024);
    }

    /**
     * Extract the maximum upload size in bytes from the configured rules.
     * Looks for a `max:N` rule (where N is in kilobytes per Laravel convention)
     * and returns the byte equivalent. Returns null if no max rule found.
     */
    public static function maxUploadSizeInBytes()
    {
        foreach (static::rules() as $rule) {
            if (! is_string($rule)) continue;

            if (str_starts_with($rule, 'max:')) {
                $kb = (int) substr($rule, 4);
                return $kb * 1024;
            }
        }

        return null;
    }

    public static function chunkSizeForS3(): int
    {
        $explicit = config('livewire.temporary_file_upload.chunk.s3_size');

        if ($explicit !== null) return max((int) $explicit, 5 * 1024 * 1024);

        return max(static::chunkSize(), 5 * 1024 * 1024);
    }

    public static function s3Client()
    {
        $adapter = invade(static::storage()->getDriver())->adapter;

        return invade($adapter)->client;
    }

    public static function s3Bucket()
    {
        $adapter = invade(static::storage()->getDriver())->adapter;

        return invade($adapter)->bucket;
    }

    public static function storeTemporaryFile($file, $disk)
    {
        $filename = TemporaryUploadedFile::generateHashName($file);
        $metaFilename = $filename . '.json';
        
        Storage::disk($disk)->put('/'.static::path($metaFilename), json_encode([
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'hash' => $file->hashName(),
        ]));

        return $file->storeAs('/'.static::path(), $filename, [
            'disk' => $disk
        ]);
    }
}
