<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\URL;

use function Livewire\invade;

class GenerateSignedUploadUrl
{
    public function forLocal()
    {
        return $this->signedRoute(
            'livewire.upload-file', now()->addMinutes(FileUploadConfiguration::maxUploadTime())
        );
    }

    public function forS3($file, $visibility = 'private')
    {
        $storage = FileUploadConfiguration::storage();

        $driver = $storage->getDriver();

        // Flysystem V2+ doesn't allow direct access to adapter, so we need to invade instead.
        $adapter = invade($driver)->adapter;

        // Flysystem V2+ doesn't allow direct access to client, so we need to invade instead.
        $client = invade($adapter)->client;

        // Flysystem V2+ doesn't allow direct access to bucket, so we need to invade instead.
        $bucket = invade($adapter)->bucket;

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

        $uri = $signedRequest->getUri();

        if (filled($url = $storage->getConfig()['temporary_url'] ?? null)) {
            $uri = invade($storage)->replaceBaseUrl($uri, $url);
        }

        return [
            'path' => TemporaryUploadedFile::signPath($fileHashName),
            'url' => (string) $uri,
            'headers' => $this->headers($signedRequest, $fileType),
        ];
    }

    // Signs relative so the scheme can't mismatch behind a proxy; re-absolutized
    // for a normal-looking URL. Validate with `hasValidRelativeSignature()`.
    public function signedRoute($name, $expiration, $parameters = [])
    {
        $relative = URL::temporarySignedRoute($name, $expiration, $parameters, false);

        return URL::to($relative);
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
