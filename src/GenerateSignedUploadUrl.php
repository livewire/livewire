<?php

namespace Livewire;

use Illuminate\Support\Facades\URL;
use League\Flysystem\Cached\CachedAdapter;

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
        $driver = FileUploadConfiguration::storage()->getDriver();

        // Flysystem V2+ doesn't allow direct access to adapter, so we need to invade instead.
        if (method_exists($driver, 'getAdapter')) {
            $adapter = $driver->getAdapter();
        } else {
            $adapter = invade($driver)->adapter;
        }

        if ($adapter instanceof CachedAdapter) {
            $adapter = $adapter->getAdapter();
        }

        // Flysystem V2+ doesn't allow direct access to client, so we need to invade instead.
        if (method_exists($adapter, 'getClient')) {
            $client = $adapter->getClient();
        } else {
            $client = invade($adapter)->client;
        }

        // Flysystem V2+ doesn't allow direct access to bucket, so we need to invade instead.
        if (method_exists($adapter, 'getBucket')) {
            $bucket = $adapter->getBucket();
        } else {
            $bucket = invade($adapter)->bucket;
        }

        $fileType = $file->getMimeType();
        $fileHashName = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
        $path = FileUploadConfiguration::path($fileHashName);

        $command = $client->getCommand('putObject', array_filter([
            'Bucket' => $bucket,
            'Key' => $path,
            'ACL' => $visibility,
            'ContentType' => $fileType ?: 'application/octet-stream',
            'CacheControl' => null,
            'Expires' => null,
        ]));

        $signedRequest = $client->createPresignedRequest(
            $command,
            '+' . FileUploadConfiguration::maxUploadTime() . ' minutes'
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
