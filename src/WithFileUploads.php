<?php

namespace Livewire;

use Illuminate\Http\UploadedFile;
use Facades\Livewire\GenerateSignedUploadUrl;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\S3DoesntSupportMultipleFileUploads;

trait WithFileUploads
{
    public function startUpload($name, $fileInfo, $isMultiple)
    {
        if (FileUploadConfiguration::isUsingS3()) {
            throw_if($isMultiple, S3DoesntSupportMultipleFileUploads::class);

            $file = UploadedFile::fake()->create('test', $fileInfo[0]['size'] / 1024, $fileInfo[0]['type']);

            $this->emitSelf('upload:generatedSignedUrlForS3', $name, GenerateSignedUploadUrl::forS3($file));

            return;
        }

        $this->emitSelf('upload:generatedSignedUrl', $name, GenerateSignedUploadUrl::forLocal());
    }

    public function finishUpload($name, $tmpPath, $isMultiple)
    {
        $this->emitSelf('upload:finished', $name);

        $this->cleanupOldUploads();

        $file = $isMultiple
            ? collect($tmpPath)->map(function ($i) {
                return TemporarilyUploadedFile::createFromLivewire($i);
            })->toArray()
            : TemporarilyUploadedFile::createFromLivewire($tmpPath[0]);

        $this->syncInput($name, $file);
    }

    public function uploadErrored($name, $errorsInJson, $isMultiple) {
        $this->emitSelf('upload:errored', $name);

        if (is_null($errorsInJson)) {
            $genericValidationMessage = trans('validation.uploaded', ['attribute' => $name]);
            if ($genericValidationMessage === 'validation.uploaded') $genericValidationMessage = "The {$name} failed to upload.";
            throw ValidationException::withMessages([$name => $genericValidationMessage]);
        }

        $errorsInJson = $isMultiple
            ? str_replace('files', $name, $errorsInJson)
            : str_replace('files.0', $name, $errorsInJson);

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
        if (FileUploadConfiguration::isUsingS3()) return;

        $storage = FileUploadConfiguration::storage();

        foreach ($storage->allFiles(FileUploadConfiguration::directory()) as $filePathname) {
            $yesterdaysStamp = now()->subDay()->timestamp;
            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }
    }
}
