<?php

namespace Livewire\Features\SupportFileUploads;

use function Livewire\invade;

trait InteractsWithS3
{
    protected function s3Client()
    {
        $driver = FileUploadConfiguration::storage()->getDriver();

        // Flysystem V2+ doesn't allow direct access to the adapter or its
        // client/bucket, so we need to invade instead...
        return invade(invade($driver)->adapter)->client;
    }

    protected function s3Bucket()
    {
        $driver = FileUploadConfiguration::storage()->getDriver();

        return invade(invade($driver)->adapter)->bucket;
    }

    protected function finalizeSignedUri($uri)
    {
        $storage = FileUploadConfiguration::storage();

        if (filled($url = $storage->getConfig()['temporary_url'] ?? null)) {
            $uri = invade($storage)->replaceBaseUrl($uri, $url);
        }

        return (string) $uri;
    }
}
