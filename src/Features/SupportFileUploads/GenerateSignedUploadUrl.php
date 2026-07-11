<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\URL;

class GenerateSignedUploadUrl
{
    use InteractsWithS3;

    public function forLocal()
    {
        return URL::temporarySignedRoute(
            'livewire.upload-file', now()->addMinutes(FileUploadConfiguration::maxUploadTime())
        );
    }

    public function forChunks()
    {
        return URL::temporarySignedRoute(
            'livewire.upload-chunk', now()->addMinutes(FileUploadConfiguration::maxUploadTime())
        );
    }

    public function forMultipart()
    {
        return URL::temporarySignedRoute(
            'livewire.upload-multipart', now()->addMinutes(FileUploadConfiguration::maxUploadTime())
        );
    }

    public function forS3($file, $visibility = 'private')
    {
        $client = $this->s3Client();

        $fileType = $file->getMimeType();
        $fileHashName = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
        $path = FileUploadConfiguration::path($fileHashName);

        $command = $client->getCommand('putObject', array_filter([
            'Bucket' => $this->s3Bucket(),
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
            'path' => TemporaryUploadedFile::signPath($fileHashName),
            'url' => $this->finalizeSignedUri($signedRequest->getUri()),
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
