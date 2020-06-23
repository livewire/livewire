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

        $bucket = $_ENV['AWS_BUCKET'];

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
        $missing = array_diff_key(array_flip(array_filter([
            'AWS_BUCKET',
            'AWS_DEFAULT_REGION',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY'
        ])), $_ENV);

        if (empty($missing)) {
            return;
        }

        throw new InvalidArgumentException(
            "Unable to issue signed URL. Missing environment variables: ".implode(', ', array_keys($missing))
        );
    }

    protected function storageClient()
    {
        $config = [
            'region' => $_ENV['AWS_DEFAULT_REGION'],
            'version' => 'latest',
            'signature_version' => 'v4',
        ];

        if (! isset($_ENV['AWS_LAMBDA_FUNCTION_VERSION'])) {
            $config['credentials'] = array_filter([
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? null,
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? null,
                'token' => $_ENV['AWS_SESSION_TOKEN'] ?? null,
                'url' => $_ENV['AWS_URL'] ?? null,
            ]);
        }

        return S3Client::factory($config);
    }
}
