<?php

namespace Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

trait WithFileUploads
{
    public function generateSignedRoute($modelName, $fileInfo, $isMultiple)
    {
        // $file = $isMultiple
        //     ? collect($fileInfo)->map(function ($i) {
        //         return LivewireNotYetUploadedFile::createFromLivewire($i);
        //     })->toArray()
        //     : LivewireNotYetUploadedFile::createFromLivewire($fileInfo[0]);

        // $this->syncInput($modelName, $file);

        // $payload = (new GeneratePreSignedS3UploadUrl)($file);

        // $this->emitSelf('generatedPreSignedS3Url', $payload);

        $signedUrl = URL::temporarySignedRoute(
            'livewire.upload-file', now()->addMinutes(5)
        );

        $this->emitSelf('generatedSignedUrl', $signedUrl);
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
