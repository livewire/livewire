<?php

namespace Livewire;

use Illuminate\Support\Facades\URL;

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
            'livewire.upload-file', now()->addMinutes(30)
        );

        $this->emitSelf('generatedSignedUrl', $signedUrl);
    }

    public function finishUpload($modelName, $tmpPath, $isMultiple)
    {
        // every 5%-100%? of requests cleans the tmp directory of files older than 24hrs.
        // on s3 auto-config /tmp cleanup

        $file = $isMultiple
            ? collect($tmpPath)->map(function ($i) {
                return TemporaryUploadedFile::createFromLivewire($i);
            })->toArray()
            : TemporaryUploadedFile::createFromLivewire($tmpPath[0]);

        $this->syncInput($modelName, $file);
    }
}
