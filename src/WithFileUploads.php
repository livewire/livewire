<?php

namespace Livewire;

use Illuminate\Support\Facades\URL;

trait WithFileUploads
{
    public function generateSignedRoute($modelName, $fileInfo, $isMultiple)
    {
        $file = $isMultiple
            ? collect($fileInfo)->map(function ($i) {
                return LivewireNotYetUploadedFile::createFromLivewire($i);
            })->toArray()
            : LivewireNotYetUploadedFile::createFromLivewire($fileInfo[0]);

        $this->syncInput($modelName, $file);

        $payload = (new GeneratePreSignedS3UploadUrl)($file);

        $this->emitSelf('generatedPreSignedS3Url', $payload);

        // $signedUrl = URL::temporarySignedRoute(
        //     'livewire.upload-file', now()->addMinutes(30)
        // );

        // $this->emitSelf('generatedSignedUrl', $signedUrl);
    }

    public function finishUpload($modelName, $tmpPath, $isMultiple)
    {
        $file = $isMultiple
            ? collect($tmpPath)->map(function ($i) {
                return LivewireUploadedFile::createFromLivewire($i);
            })->toArray()
            : LivewireUploadedFile::createFromLivewire($tmpPath[0]);

        $this->syncInput($modelName, $file);
    }
}
