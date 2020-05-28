<?php

namespace Livewire;

use Illuminate\Http\UploadedFile;
use Facades\Livewire\GenerateSignedUploadUrl;
use Livewire\Exceptions\S3DoesntSupportMultipleFileUploads;

trait WithFileUploads
{
    public function generateSignedRoute($modelName, $fileInfo, $isMultiple)
    {
        if (FileUploadConfiguration::isUsingS3()) {
            throw_if($isMultiple, S3DoesntSupportMultipleFileUploads::class);

            $file = UploadedFile::fake()->create('test', $fileInfo[0]['size'] / 1024, $fileInfo[0]['type']);

            $this->emitSelf('generatedPreSignedS3Url', GenerateSignedUploadUrl::forS3($file));

            return;
        }

        $this->emitSelf('generatedSignedUrl', GenerateSignedUploadUrl::forLocal());
    }

    public function finishUpload($modelName, $tmpPath, $isMultiple)
    {
        $this->cleanupOldUploads();

        $file = $isMultiple
            ? collect($tmpPath)->map(function ($i) {
                return TemporaryUploadedFile::createFromLivewire($i);
            })->toArray()
            : TemporaryUploadedFile::createFromLivewire($tmpPath[0]);

        $this->syncInput($modelName, $file);
    }

    protected function cleanupOldUploads()
    {
        $storage = FileUploadConfiguration::storage();

        foreach ($storage->allFiles('/tmp') as $filePathname) {
            $yesterdaysStamp = now()->subDay()->timestamp;
            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }
    }
}
