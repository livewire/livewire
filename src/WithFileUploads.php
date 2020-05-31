<?php

namespace Livewire;

use Illuminate\Http\UploadedFile;
use Facades\Livewire\GenerateSignedUploadUrl;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\S3DoesntSupportMultipleFileUploads;

trait WithFileUploads
{
    public function startUpload($modelName, $fileInfo, $isMultiple)
    {
        if (FileUploadConfiguration::isUsingS3()) {
            throw_if($isMultiple, S3DoesntSupportMultipleFileUploads::class);

            $file = UploadedFile::fake()->create('test', $fileInfo[0]['size'] / 1024, $fileInfo[0]['type']);

            $this->emitSelf('file-upload:generatedSignedUrlForS3', GenerateSignedUploadUrl::forS3($file));

            return;
        }

        $this->emitSelf('file-upload:generatedSignedUrl', GenerateSignedUploadUrl::forLocal());
    }

    public function finishUpload($modelName, $tmpPath, $isMultiple)
    {
        $this->emitSelf('file-upload:finished');

        $this->cleanupOldUploads();

        $file = $isMultiple
            ? collect($tmpPath)->map(function ($i) {
                return TemporarilyUploadedFile::createFromLivewire($i);
            })->toArray()
            : TemporarilyUploadedFile::createFromLivewire($tmpPath[0]);

        $this->syncInput($modelName, $file);
    }

    public function uploadErrored($modelName, $errorsInJson, $isMultiple) {
        $this->emitSelf('file-upload:errored');

        if (is_null($errorsInJson)) {
            $genericValidationMessage = trans('validation.uploaded', ['attribute' => $modelName]);
            if ($genericValidationMessage === 'validation.uploaded') $genericValidationMessage = "The {$modelName} failed to upload.";
            throw ValidationException::withMessages([$modelName => $genericValidationMessage]);
        }

        $errorsInJson = $isMultiple
            ? str_replace('files', $modelName, $errorsInJson)
            : str_replace('files.0', $modelName, $errorsInJson);

        $errors = json_decode($errorsInJson, true)['errors'];

        throw (ValidationException::withMessages($errors));
    }

    protected function hydratePropertyFromWithFileUploads($name, $value)
    {
        if (TemporarilyUploadedFile::canUnserialize($value)) {
            return TemporarilyUploadedFile::unserializeFromLivewireRequest($value);
        }
        return $value;
    }

    protected function dehydratePropertyFromWithFileUploads($name, $value)
    {
        if ($value instanceof TemporarilyUploadedFile) {
            return $value->serializeForLivewireResponse();
        } elseif (is_array($value) && isset($value[0]) && $value[0] instanceof TemporarilyUploadedFile) {
            return $value[0]::serializeMultipleForLivewireResponse($value);
        }
        return $value;
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
