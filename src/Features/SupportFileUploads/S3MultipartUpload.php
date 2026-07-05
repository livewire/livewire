<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Facades\GenerateSignedUploadUrlFacade;

/**
 * Drives S3 multipart uploads: large files are uploaded straight from the
 * browser in parts via presigned UploadPart URLs, then completed server-side.
 *
 * A small mapping file at "livewire-tmp/multipart/{fingerprint}.json" ties a
 * deterministic file fingerprint to the S3 UploadId. That mapping is what
 * makes these uploads resumable — when the same file is re-selected after an
 * interruption, the planner finds the in-progress multipart upload, asks S3
 * which parts it already has (ListParts), and only presigns the missing ones.
 *
 * ETag bookkeeping happens entirely server-side (via ListParts) so buckets
 * don't need an "ExposeHeaders: ETag" CORS rule.
 */
class S3MultipartUpload
{
    use InteractsWithS3;

    public function plan($fileInfo)
    {
        // Grow the part size for very large files — S3 allows at most 10,000
        // parts per multipart upload...
        $partSize = max(
            FileUploadConfiguration::chunkSize(),
            (int) ceil(max(0, $fileInfo['size']) / ChunkedUpload::MAX_CHUNKS)
        );

        $fingerprint = ChunkedUpload::fingerprint($fileInfo, $partSize);

        $mapping = $this->existing($fingerprint) ?? $this->create($fingerprint, $fileInfo, $partSize);

        $uploadedParts = $this->uploadedParts($mapping);

        $totalParts = ChunkedUpload::totalChunks($fileInfo['size'], $partSize);

        $missing = collect(range(1, $totalParts))
            ->reject(fn ($number) => isset($uploadedParts[$number]))
            ->values();

        return [
            'ref' => TemporaryUploadedFile::signPath($fingerprint),
            'partSize' => $partSize,
            'totalParts' => $totalParts,
            'uploadedParts' => $uploadedParts,
            'parts' => $missing->map(fn ($number) => [
                'partNumber' => $number,
                'url' => $this->signPartUrl($mapping, $number),
            ])->all(),
            'completeUrl' => GenerateSignedUploadUrlFacade::forMultipart(),
        ];
    }

    public function complete($fingerprint)
    {
        $mapping = $this->mapping($fingerprint);

        abort_if(is_null($mapping), 404, 'No multipart upload in progress for this file.');

        $parts = collect($this->listParts($mapping))
            ->map(fn ($part) => ['PartNumber' => $part['PartNumber'], 'ETag' => $part['ETag']])
            ->sortBy('PartNumber')->values()->all();

        $this->s3Client()->completeMultipartUpload([
            'Bucket' => $this->s3Bucket(),
            'Key' => $mapping['key'],
            'UploadId' => $mapping['uploadId'],
            'MultipartUpload' => ['Parts' => $parts],
        ]);

        $this->deleteMapping($fingerprint);

        return TemporaryUploadedFile::signPath($mapping['filename']);
    }

    public function abort($fingerprint)
    {
        $mapping = $this->mapping($fingerprint);

        if (is_null($mapping)) return;

        try {
            $this->s3Client()->abortMultipartUpload([
                'Bucket' => $this->s3Bucket(),
                'Key' => $mapping['key'],
                'UploadId' => $mapping['uploadId'],
            ]);
        } catch (\Throwable $e) {
            // The upload may have already expired or been aborted...
        }

        $this->deleteMapping($fingerprint);
    }

    protected function create($fingerprint, $fileInfo, $partSize)
    {
        $filename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($fileInfo['name']);

        $key = FileUploadConfiguration::path($filename);

        $result = $this->s3Client()->createMultipartUpload(array_filter([
            'Bucket' => $this->s3Bucket(),
            'Key' => $key,
            'ACL' => 'private',
            'ContentType' => $fileInfo['type'] ?: 'application/octet-stream',
        ]));

        $mapping = [
            'filename' => $filename,
            'key' => $key,
            'uploadId' => $result['UploadId'],
            'partSize' => $partSize,
            'name' => $fileInfo['name'],
            'size' => $fileInfo['size'],
        ];

        FileUploadConfiguration::storage()->put($this->mappingPath($fingerprint), json_encode($mapping));

        return $mapping;
    }

    protected function existing($fingerprint)
    {
        $mapping = $this->mapping($fingerprint);

        if (is_null($mapping)) return null;

        try {
            $this->listParts($mapping, limit: 1);
        } catch (\Throwable $e) {
            // The multipart upload expired or was aborted out from under us,
            // so throw the stale mapping away and start fresh...
            $this->deleteMapping($fingerprint);

            return null;
        }

        return $mapping;
    }

    protected function uploadedParts($mapping)
    {
        return collect($this->listParts($mapping))
            ->mapWithKeys(fn ($part) => [(int) $part['PartNumber'] => (int) $part['Size']])
            ->all();
    }

    protected function listParts($mapping, $limit = null)
    {
        $parts = [];
        $marker = 0;

        do {
            $result = $this->s3Client()->listParts(array_filter([
                'Bucket' => $this->s3Bucket(),
                'Key' => $mapping['key'],
                'UploadId' => $mapping['uploadId'],
                'PartNumberMarker' => $marker,
                'MaxParts' => $limit,
            ], fn ($value) => ! is_null($value) && $value !== 0));

            $parts = array_merge($parts, $result['Parts'] ?? []);

            $marker = $result['NextPartNumberMarker'] ?? null;
        } while (! $limit && ($result['IsTruncated'] ?? false) && $marker);

        return $parts;
    }

    protected function signPartUrl($mapping, $partNumber)
    {
        $command = $this->s3Client()->getCommand('uploadPart', [
            'Bucket' => $this->s3Bucket(),
            'Key' => $mapping['key'],
            'UploadId' => $mapping['uploadId'],
            'PartNumber' => $partNumber,
        ]);

        $signedRequest = $this->s3Client()->createPresignedRequest(
            $command,
            '+'.FileUploadConfiguration::maxUploadTime().' minutes'
        );

        return $this->finalizeSignedUri($signedRequest->getUri());
    }

    protected function mapping($fingerprint)
    {
        $contents = FileUploadConfiguration::storage()->get($this->mappingPath($fingerprint));

        return $contents ? json_decode($contents, true) : null;
    }

    protected function deleteMapping($fingerprint)
    {
        FileUploadConfiguration::storage()->delete($this->mappingPath($fingerprint));
    }

    protected function mappingPath($fingerprint)
    {
        return FileUploadConfiguration::path('multipart/'.$fingerprint.'.json', false);
    }
}
