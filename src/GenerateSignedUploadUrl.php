<?php

namespace Livewire;

use Illuminate\Support\Facades\URL;

class GenerateSignedUploadUrl
{
    public function forLocal()
    {
        return URL::temporarySignedRoute(
            'livewire.upload-file', now()->addMinutes(5)
        );
    }

    public function forS3($file, $visibility = 'private')
    {
        $adapter = FileUploadConfiguration::storage()->getDriver()->getAdapter();

        $fileType = $file->getMimeType();
        $fileHashName = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
        $path = FileUploadConfiguration::path($fileHashName);

        $command = $adapter->getClient()->getCommand('putObject', array_filter([
            'Bucket' => $adapter->getBucket(),
            'Key' => $path,
            'ACL' => $visibility,
            'ContentType' => $fileType ?: 'application/octet-stream',
            'CacheControl' => null,
            'Expires' => null,
        ]));

        $signedRequest = $adapter->getClient()->createPresignedRequest(
            $command,
            '+5 minutes'
        );

        return [
            'path' => $fileHashName,
            'url' => (string) $signedRequest->getUri(),
            'headers' => $this->headers($signedRequest, $fileType),
        ];
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
