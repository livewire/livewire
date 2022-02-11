<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use League\Flysystem\Cached\CachedAdapter;
use Livewire\FileUploadConfiguration;
use function Livewire\invade;

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

        $client->putBucketLifecycleConfiguration([
            'Bucket' => $bucket,
            'LifecycleConfiguration' => [
                'Rules' => [
                    [
                        'Prefix' => $prefix = FileUploadConfiguration::path(),
                        'Expiration' => [
                            'Days' => 1,
                        ],
                        'Status' => 'Enabled',
                    ],
                ],
            ],
        ]);

        $this->info('Livewire temporary S3 upload directory ['.$prefix.'] set to automatically cleanup files older than 24hrs!');
    }
}
