<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\URL;

use function Livewire\invade;

class GenerateSignedUploadUrl
{
    public function forLocal()
    {
        return URL::temporarySignedRoute(
            'livewire.upload-file', now()->addMinutes(FileUploadConfiguration::maxUploadTime())
        );
    }

    public function forS3($file, $visibility = 'private')
    {
        $fileType = $file->getMimeType();
        $fileHashName = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
        $path = FileUploadConfiguration::path($fileHashName);

        $shouldUseMultipartUpload = $file->getSize() > FileUploadConfiguration::maxUploadPartSize();
        if ($shouldUseMultipartUpload) {
            return $this->forS3Multipart($file, $visibility);
        }

        $command = $this->client()->getCommand('putObject', array_filter([
            'Bucket' => $this->bucket(),
            'Key' => $path,
            'ACL' => $visibility,
            'ContentType' => $fileType ?: 'application/octet-stream',
            'CacheControl' => null,
            'Expires' => null,
        ]));

        $signedRequest = $this->client()->createPresignedRequest(
            $command,
            '+' . FileUploadConfiguration::maxUploadTime() . ' minutes'
        );

        return [
            'path' => $fileHashName,
            'url' => (string) $signedRequest->getUri(),
            'headers' => $this->headers($signedRequest, $fileType),
        ];
    }

    public function forMultipartUpload($fileHashName, $uploadId, $partNumber, $partsCount)
    {
        $signedRequest = $this->signRequestUploadPart($fileHashName, $uploadId, $partNumber);

        return [
            'path' => $fileHashName,
            'upload_id' => $uploadId,
            'url' => (string) $signedRequest->getUri(),
            'headers' => $this->headers($signedRequest, null),
            'next_part' => $partNumber + 1,
            'parts_count' => $partsCount,
        ];
    }

    public function completeMultipartUpload($key, $uploadId, $parts)
    {
        $response = $this->client()->completeMultipartUpload([
            'Bucket'          => $this->bucket(),
            'Key'             => $key,
            'UploadId'        => $uploadId,
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ]);

        return [
            'location' => $response['Location'],
        ];
    }

    protected function forS3Multipart($file, $visibility = 'private')
    {
        $fileType = $file->getMimeType();
        $fileHashName = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
        $path = FileUploadConfiguration::path($fileHashName);

        $result = $this->client()->createMultipartUpload([
            'Bucket' => $this->bucket(),
            'Key' => $path,
            'ACL' => $visibility,
            'CacheControl' => null,
            'Expires' => null,
        ]);

        $uploadId = $result['UploadId'];

        $signedRequest = $this->signRequestUploadPart($fileHashName, $fileType, $uploadId, $partNumber = 0);

        return [
            'path' => $fileHashName,
            'upload_id' => $uploadId,
            'url' => (string) $signedRequest->getUri(),
            'headers' => $this->headers($signedRequest, $fileType),
            'parts_count' => ceil($file->getSize() / FileUploadConfiguration::maxUploadPartSize()),
            'next_part' => $partNumber + 1,
        ];
    }

    protected function signRequestUploadPart($fileHashName, $uploadId, $partNumber)
    {
        $command = $this->client()->getCommand('UploadPart', [
            'Bucket'     => $this->bucket(),
            'Key'        => FileUploadConfiguration::path($fileHashName),
            'UploadId'   => $uploadId,
            'PartNumber' => (int) $partNumber,
        ]);

        return $this->client()->createPresignedRequest(
            $command,
            '+' . FileUploadConfiguration::maxUploadTime() . ' minutes',
        );
    }

    protected function adapter()
    {
        // Flysystem V2+ doesn't allow direct access to adapter, so we need to invade instead.
        return invade(FileUploadConfiguration::storage()
                    ->getDriver())->adapter;
    }

    /**
     * Get the client
     *
     * Flysystem V2+ doesn't allow direct access to client, so we need to invade instead.
     *
     * @return \Aws\S3\S3Client
     */
    protected function client()
    {
        return invade($this->adapter())->client;
    }

    protected function bucket()
    {
        // Flysystem V2+ doesn't allow direct access to bucket, so we need to invade instead.
        return invade($this->adapter())->bucket;
    }

    protected function headers($signedRequest, $fileType)
    {
        return array_merge(
            $signedRequest->getHeaders(),
            [
                'Content-Type' => $fileType ?: 'application/octet-stream'
            ]
        );
    }
}
