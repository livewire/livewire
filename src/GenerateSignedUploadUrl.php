<?php

namespace Livewire;

use Aws\S3\S3Client;
use InvalidArgumentException;
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
        $this->ensureEnvironmentVariablesAreAvailable();

        $bucket = env('AWS_BUCKET');

        $fileType = $file->getMimeType();

        $client = $this->storageClient();

        $fileHashName = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);

        $path = FileUploadConfiguration::path($fileHashName);

        $signedRequest = $client->createPresignedRequest(
            $this->createCommand($client, $bucket, $path, $fileType, $visibility),
            '+5 minutes'
        );

        $uri = $signedRequest->getUri();

        return [
            'path' => $fileHashName,
            'url' => 'https://'.$uri->getHost().$uri->getPath().'?'.$uri->getQuery(),
            'headers' => $this->headers($signedRequest, $fileType),
        ];
    }

    protected function createCommand(S3Client $client, $bucket, $key, $fileType, $visibility)
    {
        return $client->getCommand('putObject', array_filter([
            'Bucket' => $bucket,
            'Key' => $key,
            'ACL' => $visibility,
            'ContentType' => $fileType ?: 'application/octet-stream',
            'CacheControl' => null,
            'Expires' => null,
        ]));
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

    protected function ensureEnvironmentVariablesAreAvailable()
    {
        $unavailableVariables = array_filter([
            'AWS_BUCKET',
            'AWS_DEFAULT_REGION',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
        ], function ($variable) {
            return (bool) ! env($variable);
        });

        if (empty($unavailableVariables)) {
            return;
        }

        throw new InvalidArgumentException(
            "Unable to issue signed URL. Missing environment variables: ".implode(', ', array_keys($missing))
        );
    }

    protected function storageClient()
    {
        $config = [
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'signature_version' => 'v4',
        ];

        if (! env('AWS_LAMBDA_FUNCTION_VERSION')) {
            $config['credentials'] = array_filter([
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'token' => env('AWS_SESSION_TOKEN'),
                'url' => env('AWS_URL'),
            ]);
        }

        return S3Client::factory($config);
    }
}
