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
    public function plan($fileInfos, $isMultiple)
    {
        $fileInfos = collect($fileInfos)->values()->map(fn ($info) => [
            'name' => (string) ($info['name'] ?? ''),
            'size' => (int) ($info['size'] ?? 0),
            'type' => (string) ($info['type'] ?? ''),
            'lastModified' => (int) ($info['lastModified'] ?? 0),
        ]);

        if ($errors = $this->declaredSizeViolations($fileInfos)) {
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
        $chunkSize = FileUploadConfiguration::chunkSize();

        return [
            'strategy' => 'chunked',
            'url' => GenerateSignedUploadUrlFacade::forChunks(),
            'chunkSize' => $chunkSize,
            'files' => $fileInfos->map(function ($info) use ($chunkSize) {
                $id = ChunkedUpload::fingerprint($info, $chunkSize);

                return [
                    'id' => TemporaryUploadedFile::signPath($id),
                    'totalChunks' => ChunkedUpload::totalChunks($info['size'], $chunkSize),
                    // Chunks that already made it to the server from a previous
                    // attempt — the frontend skips these so uploads are resumable...
                    'receivedChunks' => ChunkedUpload::receivedChunks($id),
                ];
            })->all(),
        ];
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

    protected function declaredSizeViolations($fileInfos)
    {
        $maxKilobytes = FileUploadConfiguration::maxDeclaredSizeInKilobytes();

        if (! $maxKilobytes) return null;

        $errors = [];

        foreach ($fileInfos as $index => $info) {
            if ($info['size'] > $maxKilobytes * 1024) {
                $message = trans('validation.max.file', ['attribute' => 'files.'.$index, 'max' => $maxKilobytes]);

                $errors['files.'.$index] = [$message];
            }
        }

        return $errors ?: null;
    }
}
