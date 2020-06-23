<?php

namespace Livewire\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Livewire\FileUploadConfiguration;

class S3CleanupCommand extends Command
{
    protected $signature = 'livewire:configure-s3-upload-cleanup';

    protected $description = 'Configure temporary file upload s3 directory to automatically cleanup files older than 24hrs.';

    public function handle()
    {
        if (! FileUploadConfiguration::isUsingS3()) {
            $this->error("Configuration ['livewire.temporary_file_upload.disk'] is not set to a disk with an S3 driver.");
            return;
        }

        $diskConfig = FileUploadConfiguration::diskConfig();
        $bucket = $diskConfig['bucket'];
        $prefix = FileUploadConfiguration::path();
        $days = 1;

        $client = $this->storageClient($diskConfig);

        $client->putBucketLifecycleConfiguration([
            'Bucket' => $bucket,
            'LifecycleConfiguration' => [
                'Rules' => [
                    [
                        'Prefix' => $prefix,
                        'Expiration' => [
                            'Days' => $days,
                        ],
                        'Status' => 'Enabled',
                    ],
                ],
            ],
        ]);

        $this->info('Livewire temporary S3 upload directory ['.$prefix.'] set to automatically cleanup files older than 24hrs!');
    }

    public function storageClient($diskConfig)
    {
        $config = [
            'region' => $diskConfig['region'],
            'version' => 'latest',
            'signature_version' => 'v4',
        ];

        $config['credentials'] = array_filter([
            'key' => $diskConfig['key'] ?? null,
            'secret' => $diskConfig['secret'] ?? null,
            'token' => $_ENV['AWS_SESSION_TOKEN'] ?? null,
            'url' => $diskConfig['url'] ?? null,
        ]);

        return S3Client::factory($config);
    }
}
