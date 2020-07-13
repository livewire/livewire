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

            $file = UploadedFile::fake()->create($fileInfo[0]['name'], $fileInfo[0]['size'] / 1024, $fileInfo[0]['type']);

            $this->emitSelf('upload:generatedSignedUrlForS3', $name, GenerateSignedUploadUrl::forS3($file));

            return;
        }

        $this->emitSelf('upload:generatedSignedUrl', $name, GenerateSignedUploadUrl::forLocal());
    }

    public function finishUpload($name, $tmpPath, $isMultiple)
    {
        $this->cleanupOldUploads();

        if ($isMultiple) {
            $file = collect($tmpPath)->map(function ($i) {
                return TemporaryUploadedFile::createFromLivewire($i);
            })->toArray();
            $this->emitSelf('upload:finished', $name, collect($file)->map->getFilename()->toArray());
        } else {
            $file = TemporaryUploadedFile::createFromLivewire($tmpPath[0]);
            $this->emitSelf('upload:finished', $name, [$file->getFilename()]);

            // If the property is an array, but the upload ISNT set to "multiple"
            // then APPEND the upload to the array, rather than replacing it.
            if (is_array($value = $this->getPropertyValue($name))) {
                $file = array_merge($value, [$file]);
            }
        }

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

    public function removeUpload($name, $tmpFilename)
    {
        $uploads = $this->getPropertyValue($name);

        if (is_array($uploads) && isset($uploads[0]) && $uploads[0] instanceof TemporaryUploadedFile) {
            $this->emitSelf('upload:removed', $name, $tmpFilename);

            $this->syncInput($name, array_values(array_filter($uploads, function ($upload) use ($tmpFilename) {
                if ($upload->getFilename() === $tmpFilename) {
                    $upload->delete();
                    return false;
                }

                return true;
            })));
        } elseif ($uploads instanceof TemporaryUploadedFile) {
            $uploads->delete();

            $this->emitSelf('upload:removed', $name, $tmpFilename);

            if ($uploads->getFilename() === $tmpFilename) $this->syncInput($name, null);
        }
    }

    protected function hydratePropertyFromWithFileUploads($name, $value)
    {
        if (TemporaryUploadedFile::canUnserialize($value)) {
            return TemporaryUploadedFile::unserializeFromLivewireRequest($value);
        }

        return $value;
    }

    protected function dehydratePropertyFromWithFileUploads($name, $value)
    {
        if ($value instanceof TemporaryUploadedFile) {
            return $value->serializeForLivewireResponse();
        } elseif (is_array($value) && isset(array_values($value)[0]) && array_values($value)[0] instanceof TemporaryUploadedFile) {
            return array_values($value)[0]::serializeMultipleForLivewireResponse($value);
        }

        return $value;
    }

    protected function cleanupOldUploads()
    {
        if (FileUploadConfiguration::isUsingS3()) return;

        $storage = FileUploadConfiguration::storage();

        foreach ($storage->allFiles(FileUploadConfiguration::path()) as $filePathname) {
            $yesterdaysStamp = now()->subDay()->timestamp;
            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }
    }
}
