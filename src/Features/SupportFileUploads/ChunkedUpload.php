<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\UploadedFile;

/**
 * Storage and assembly of chunked uploads on non-S3 disks.
 *
 * Chunks live at "livewire-tmp/chunks/{fingerprint}/{index}.part" until the
 * final chunk arrives, at which point they are stitched together, validated
 * against the configured upload rules, and stored like any other temporary
 * upload. The fingerprint is deterministic for a given file, which is what
 * makes interrupted uploads resumable — a re-selected file maps back to the
 * chunks that already made it to the server.
 */
class ChunkedUpload
{
    // Also S3's hard limit on multipart upload parts...
    const MAX_CHUNKS = 10000;

    public static function fingerprint($fileInfo, $chunkSize)
    {
        return sha1(implode('|', [
            // Scoped to the session so no other user can derive (and obtain a
            // signed reference to) the fingerprint of someone else's upload...
            session()->getId(),
            $fileInfo['name'],
            $fileInfo['size'],
            $fileInfo['type'] ?? '',
            $fileInfo['lastModified'] ?? 0,
            $chunkSize,
        ]));
    }

    public static function signCapability($fingerprint, $totalChunks, $chunkSize)
    {
        return TemporaryUploadedFile::signPath($fingerprint.'|'.$totalChunks.'|'.$chunkSize);
    }

    public static function verifyCapability($id)
    {
        $payload = is_string($id) ? TemporaryUploadedFile::extractPathFromSignedPath($id) : false;

        if ($payload === false || ! preg_match('/^([a-f0-9]{40})\|(\d{1,5})\|(\d{1,10})$/', $payload, $matches)) {
            return false;
        }

        [, $fingerprint, $totalChunks, $chunkSize] = $matches;

        if ((int) $totalChunks < 1 || (int) $totalChunks > static::MAX_CHUNKS || (int) $chunkSize < 1) {
            return false;
        }

        return [$fingerprint, (int) $totalChunks, (int) $chunkSize];
    }

    public static function totalChunks($size, $chunkSize)
    {
        return max(1, (int) ceil($size / $chunkSize));
    }

    public static function directory($id)
    {
        return FileUploadConfiguration::path('chunks/'.$id, false);
    }

    public static function receivedChunks($id)
    {
        $storage = FileUploadConfiguration::storage();

        return collect($storage->files(static::directory($id)))
            ->map(fn ($path) => basename($path))
            ->filter(fn ($name) => preg_match('/^\d+\.part$/', $name))
            ->map(fn ($name) => (int) $name)
            ->sort()->values()->all();
    }

    public static function storeChunk($id, $index, $chunk)
    {
        FileUploadConfiguration::storage()->putFileAs(
            static::directory($id), $chunk, $index.'.part'
        );
    }

    public static function assemble($id, $fileInfo, $disk)
    {
        $storage = FileUploadConfiguration::storage();

        $tmpPath = tempnam(sys_get_temp_dir(), 'livewire-chunked');

        try {
            $out = fopen($tmpPath, 'wb');

            foreach (static::receivedChunks($id) as $index) {
                $in = $storage->readStream(static::directory($id).'/'.$index.'.part');

                // A chunk can vanish mid-assembly (cleanup, a concurrent request)...
                abort_unless(is_resource($in), 422, 'The chunked upload is incomplete.');

                stream_copy_to_stream($in, $out);

                fclose($in);
            }

            fclose($out);

            $file = new UploadedFile($tmpPath, $fileInfo['name'], $fileInfo['type'] ?: null, null, true);

            // The assembled file goes through the exact same validation and
            // storage path as a directly-uploaded file...
            $paths = (new FileUploadController)->validateAndStore([$file], $disk);
        } finally {
            @unlink($tmpPath);

            $storage->deleteDirectory(static::directory($id));
        }

        return $paths->first();
    }
}
