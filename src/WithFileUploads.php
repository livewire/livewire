<?php

namespace Livewire;

use Illuminate\Http\UploadedFile;
use Facades\Livewire\GenerateSignedUploadUrl;
use Illuminate\Validation\ValidationException;

trait WithFileUploads
{
    public function requestUpload($name, $fileInfo)
    {
        if (FileUploadConfiguration::isUsingS3()) {
            $file = UploadedFile::fake()
                ->create($fileInfo['name'], $fileInfo['size'] / 1024, $fileInfo['type']);

            return $this->emitSelf('upload:generatedSignedUrlForS3', $name, $fileInfo, GenerateSignedUploadUrl::forS3($file));
        }

        $this->emitSelf('upload:generatedSignedUrl', $name, $fileInfo, GenerateSignedUploadUrl::forLocal());
    }

    public function finishUpload($name, $fileInfo, $path)
    {
        $this->cleanupOldUploads();

        $file = $this->createTemporaryUploadedFile($path);

        $this->emitSelf('upload:finished', $name, $fileInfo);

        if (is_array($value = $this->getPropertyValue($name)) || (bool) $fileInfo['multiple']) {
            $file = array_merge((array) $value, [$file]);
        }

        $this->syncInput($name, $file);
    }

    public function uploadErrored($name, $errorsInJson, $isMultiple) {
        $this->emit('upload:errored', $name)->self();

        if (is_null($errorsInJson)) {
            $genericValidationMessage = trans('validation.uploaded', ['attribute' => $name]);
            if ($genericValidationMessage === 'validation.uploaded') $genericValidationMessage = "The {$name} failed to upload.";
            throw ValidationException::withMessages([$name => $genericValidationMessage]);
        }

        $errorsInJson = $isMultiple
            ? str_ireplace('files', $name, $errorsInJson)
            : str_ireplace('files.0', $name, $errorsInJson);

        $errors = json_decode($errorsInJson, true)['errors'];

        throw (ValidationException::withMessages($errors));
    }

    public function removeUpload($name, $tmpFilename)
    {
        $uploads = $this->getPropertyValue($name);

        if (is_array($uploads) && isset($uploads[0]) && $uploads[0] instanceof TemporaryUploadedFile) {
            $this->emit('upload:removed', $name, $tmpFilename)->self();

            $this->syncInput($name, array_values(array_filter($uploads, function ($upload) use ($tmpFilename) {
                if ($upload->getFilename() === $tmpFilename) {
                    $upload->delete();
                    return false;
                }

                return true;
            })));
        } elseif ($uploads instanceof TemporaryUploadedFile) {
            $uploads->delete();

            $this->emit('upload:removed', $name, $tmpFilename)->self();

            if ($uploads->getFilename() === $tmpFilename) $this->syncInput($name, null);
        }
    }

    protected function createTemporaryUploadedFile($path): TemporaryUploadedFile
    {
        return TemporaryUploadedFile::createFromLivewire($path);
    }

    protected function cleanupOldUploads()
    {
        if (FileUploadConfiguration::isUsingS3()) return;

        $storage = FileUploadConfiguration::storage();

        foreach ($storage->allFiles(FileUploadConfiguration::path()) as $filePathname) {
            // On busy websites, this cleanup code can run in multiple threads causing part of the output
            // of allFiles() to have already been deleted by another thread.
            if (! $storage->exists($filePathname)) continue;

            $yesterdaysStamp = now()->subDay()->timestamp;
            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }
    }
}
