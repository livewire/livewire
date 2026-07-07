<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Aws\S3\S3Client;
use function array_merge;
use function Livewire\invade;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:configure-s3-upload-cleanup')]
class S3CleanupCommand extends Command
{
    protected $signature = 'livewire:configure-s3-upload-cleanup';

    protected $description = 'Configure temporary file upload s3 directory to automatically cleanup files older than 24hrs';

    public function handle()
    {
        if (! FileUploadConfiguration::isUsingS3()) {
            $this->error("Configuration ['livewire.temporary_file_upload.disk'] is not set to a disk with an S3 driver.");

            return;
        }

        $driver = FileUploadConfiguration::storage()->getDriver();

        // Flysystem V2+ doesn't allow direct access to adapter, so we need to invade instead.
        $adapter = invade($driver)->adapter;

        // Flysystem V2+ doesn't allow direct access to client, so we need to invade instead.
        $client = invade($adapter)->client;

        // Flysystem V2+ doesn't allow direct access to bucket, so we need to invade instead.
        $bucket = invade($adapter)->bucket;

        $prefix = FileUploadConfiguration::path();

        $rules[] = [
            'Filter' => [
                'Prefix' => $prefix,
            ],
            'Expiration' => [
                'Days' => 1,
            ],
            // Completed temporary objects expire above, but abandoned S3
            // multipart uploads (cancelled/interrupted chunked uploads) are not
            // objects — their uploaded parts hold invisible, billable storage
            // until this rule aborts them...
            'AbortIncompleteMultipartUpload' => [
                'DaysAfterInitiation' => 1,
            ],
            'Status' => 'Enabled',
        ];

        $rules = $this->mergeRulesWithExistingConfiguration($client, $bucket, $prefix, $rules);

        try {
            $client->putBucketLifecycleConfiguration([
                'Bucket' => $bucket,
                'LifecycleConfiguration' => [
                    'Rules' => $rules
                ],
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to configure S3 bucket ['.$bucket.'] to automatically cleanup files older than 24hrs!');
            $this->error($e->getMessage());

            return;
        }

        $this->info('Livewire temporary S3 upload directory ['.$prefix.'] set to automatically cleanup files older than 24hrs!');
    }

    private function mergeRulesWithExistingConfiguration(S3Client $client, string $bucket, string $prefix, array $rules): array
    {
        try {
            $existingConfiguration = $client->getBucketLifecycleConfiguration([
                'Bucket' => $bucket,
            ]);
        } catch (\Exception $e) {
            // if no configuration exists, we'll just ignore the error and continue.
            $existingConfiguration = null;
        }

        if ($existingConfiguration) {
            // Drop any prior rule for our prefix and re-add the current one, so
            // re-running the command upgrades an older rule (e.g. one missing
            // the AbortIncompleteMultipartUpload action) instead of no-op'ing...
            $existingRules = collect($existingConfiguration['Rules'])
                ->reject(fn ($rule) => data_get($rule, 'Filter.Prefix') === $prefix)
                ->values()
                ->all();

            $rules = array_merge($existingRules, $rules);
        }

        return $rules;
    }
}
