<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\UploadedFile;
use Livewire\Facades\GenerateSignedUploadUrlFacade;
use Livewire\Facades\S3MultipartUploadFacade;

/**
 * Given the metadata of the files a user has selected, decide HOW they
 * should be uploaded and hand the frontend everything it needs to do so.
 *
 * Strategies:
 *   "form"    → POST all files to the signed upload endpoint (small files, non-S3 disks)
 *   "chunked" → slice each file and POST chunks to the chunk endpoint (large files, non-S3 disks)
 *   "s3"      → PUT each file to a presigned URL; large files use S3 multipart via per-part presigned URLs
 *   "reject"  → the declared file sizes already violate the configured rules, fail before any bytes move
 */
class UploadPlanner
{
    public function plan($fileInfos, $isMultiple, $sizeRules = [])
    {
        $fileInfos = collect($fileInfos)->values()->map(fn ($info) => [
            'name' => is_string($info['name'] ?? null) ? $info['name'] : '',
            'size' => max(0, (int) ($info['size'] ?? 0)),
            'type' => is_string($info['type'] ?? null) ? $info['type'] : '',
            'lastModified' => (int) ($info['lastModified'] ?? 0),
        ]);

        if ($errors = $this->declaredSizeViolations($fileInfos, $sizeRules)) {
            return ['strategy' => 'reject', 'errors' => json_encode(['errors' => $errors])];
        }

        if (FileUploadConfiguration::isUsingS3()) {
            return $this->planForS3($fileInfos);
        }

        if ($this->shouldChunk($fileInfos)) {
            return $this->planForChunked($fileInfos);
        }

        return [
            'strategy' => 'form',
            'url' => GenerateSignedUploadUrlFacade::forLocal(),
        ];
    }

    protected function shouldChunk($fileInfos)
    {
        if (! FileUploadConfiguration::chunkingEnabled()) return false;

        return $fileInfos->contains(fn ($info) => $info['size'] > FileUploadConfiguration::chunkThreshold());
    }

    protected function planForChunked($fileInfos)
    {
        return [
            'strategy' => 'chunked',
            'url' => GenerateSignedUploadUrlFacade::forChunks(),
            'files' => $fileInfos->map(function ($info) {
                $chunkSize = $this->chunkSizeFor($info['size']);
                $totalChunks = ChunkedUpload::totalChunks($info['size'], $chunkSize);
                $fingerprint = ChunkedUpload::fingerprint($info, $chunkSize);

                return [
                    // A signed capability — the chunk endpoint takes the chunk
                    // count and size from here, not from request input...
                    'id' => ChunkedUpload::signCapability($fingerprint, $totalChunks, $chunkSize),
                    'chunkSize' => $chunkSize,
                    'totalChunks' => $totalChunks,
                    // Chunks that already made it to the server from a previous
                    // attempt — the frontend skips these so uploads are resumable...
                    'receivedChunks' => ChunkedUpload::receivedChunks($fingerprint),
                    // Already fully assembled on a previous attempt (a lost
                    // completion response before reload) — the frontend returns
                    // this straight away instead of re-uploading...
                    'completed' => ChunkedUpload::completedPath($fingerprint),
                ];
            })->all(),
        ];
    }

    protected function chunkSizeFor($size)
    {
        // Grow the chunk size for very large files so no upload ever needs
        // more than 10,000 chunks (also S3's limit on multipart parts)...
        return max(FileUploadConfiguration::chunkSize(), (int) ceil($size / ChunkedUpload::MAX_CHUNKS));
    }

    protected function planForS3($fileInfos)
    {
        $useMultipart = FileUploadConfiguration::chunkingEnabled();
        $threshold = FileUploadConfiguration::chunkThreshold();

        return [
            'strategy' => 's3',
            'files' => $fileInfos->map(function ($info) use ($useMultipart, $threshold) {
                if ($useMultipart && $info['size'] > $threshold) {
                    return ['multipart' => S3MultipartUploadFacade::plan($info)];
                }

                $file = UploadedFile::fake()->create($info['name'], $info['size'] / 1024, $info['type']);

                return GenerateSignedUploadUrlFacade::forS3($file);
            })->all(),
        ];
    }

    protected function declaredSizeViolations($fileInfos, $sizeRules = [])
    {
        $maxKilobytes = FileUploadConfiguration::maxDeclaredSizeInKilobytes();

        if (! $maxKilobytes && ! $sizeRules) return null;

        $errors = [];

        foreach ($fileInfos as $index => $info) {
            if ($message = $this->declaredSizeViolation($info['size'], $maxKilobytes, $sizeRules, 'files.'.$index)) {
                $errors['files.'.$index] = [$message];
            }
        }

        return $errors ?: null;
    }

    protected function declaredSizeViolation($bytes, $globalMaxKilobytes, $sizeRules, $attribute)
    {
        // These comparisons mirror Laravel's file size validation exactly
        // (kilobytes, boundary-inclusive) so a file the preflight lets through
        // never fails the same rule after upload — and vice versa...
        if ($globalMaxKilobytes && $bytes > $globalMaxKilobytes * 1024) {
            return trans('validation.max.file', ['attribute' => $attribute, 'max' => $globalMaxKilobytes]);
        }

        if (isset($sizeRules['max']) && $bytes > $sizeRules['max'] * 1024) {
            return trans('validation.max.file', ['attribute' => $attribute, 'max' => $sizeRules['max']]);
        }

        if (isset($sizeRules['min']) && $bytes < $sizeRules['min'] * 1024) {
            return trans('validation.min.file', ['attribute' => $attribute, 'min' => $sizeRules['min']]);
        }

        if (isset($sizeRules['size']) && $bytes !== $sizeRules['size'] * 1024) {
            return trans('validation.size.file', ['attribute' => $attribute, 'size' => $sizeRules['size']]);
        }

        if (isset($sizeRules['between']) && ($bytes < $sizeRules['between'][0] * 1024 || $bytes > $sizeRules['between'][1] * 1024)) {
            return trans('validation.between.file', ['attribute' => $attribute, 'min' => $sizeRules['between'][0], 'max' => $sizeRules['between'][1]]);
        }

        return null;
    }
}
