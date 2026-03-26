<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Facades\GenerateSignedUploadUrlFacade;

trait WithFileUploads
{
    #[Renderless]
    function _startUpload($name, $fileInfo, $isMultiple)
    {
        if (FileUploadConfiguration::isUsingS3()) {
            throw_if($isMultiple, S3DoesntSupportMultipleFileUploads::class);

            $file = UploadedFile::fake()->create($fileInfo[0]['name'], $fileInfo[0]['size'] / 1024, $fileInfo[0]['type']);

            $this->dispatch('upload:generatedSignedUrlForS3', name: $name, payload: GenerateSignedUploadUrlFacade::forS3($file))->self();

            return;
        }

        $this->dispatch('upload:generatedSignedUrl', name: $name, url: GenerateSignedUploadUrlFacade::forLocal())->self();
    }

    function _finishUpload($name, $tmpPath, $isMultiple, $append = true)
    {
        if (FileUploadConfiguration::shouldCleanupOldUploads()) {
            $this->cleanupOldUploads();
        }

        // Verify and extract paths from signed references.
        $tmpPath = collect($tmpPath)->map(function ($signedPath) {
            $path = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);

            if ($path === false) {
                abort(403, 'Invalid upload reference.');
            }

            return $path;
        })->toArray();

        if ($isMultiple) {
            $file = collect($tmpPath)->map(function ($i) {
                return TemporaryUploadedFile::createFromLivewire($i);
            })->toArray();
            $this->dispatch('upload:finished', name: $name, tmpFilenames: collect($file)->map->getFilename()->toArray())->self();

            if ($append) {
                $existing = $this->getPropertyValue($name);
                if ($existing instanceof \Illuminate\Support\Collection) {
                    $file = $existing->merge($file);
                } elseif (is_array($existing)) {
                    $file = array_merge($existing, $file);
                }
            }
        } else {
            $file = TemporaryUploadedFile::createFromLivewire($tmpPath[0]);
            $this->dispatch('upload:finished', name: $name, tmpFilenames: [$file->getFilename()])->self();

            // If the property is an array, but the upload ISNT set to "multiple"
            // then APPEND the upload to the array, rather than replacing it.
            if (is_array($value = $this->getPropertyValue($name))) {
                $file = array_merge($value, [$file]);
            }
        }

        app('livewire')->updateProperty($this, $name, $file);
    }

    #[Renderless]
    function _startChunkedUpload($name, $fileInfo, $isMultiple)
    {
        if (FileUploadConfiguration::isUsingS3()) {
            throw new \Exception('Chunked uploads are not supported with S3 storage.');
        }

        $uploadId = (string) Str::uuid();

        $chunkedUploads = session()->get('livewire_chunked_uploads', []);
        $chunkedUploads[$uploadId] = [
            'fileName' => $fileInfo[0]['name'],
            'fileSize' => $fileInfo[0]['size'],
            'fileMimeType' => $fileInfo[0]['type'],
        ];
        session()->put('livewire_chunked_uploads', $chunkedUploads);

        $this->dispatch('upload:generatedSignedChunkUrl', name: $name, url: GenerateSignedUploadUrlFacade::forLocalChunked($uploadId), uploadId: $uploadId)->self();
    }

    #[Renderless]
    function _cancelChunkedUpload($uploadId)
    {
        if (! Str::isUuid($uploadId)) {
            return;
        }

        $uploads = session()->get('livewire_chunked_uploads', []);

        if (! array_key_exists($uploadId, $uploads)) {
            return;
        }

        $storage = FileUploadConfiguration::storage();
        $chunkDir = FileUploadConfiguration::path('chunks/' . $uploadId);

        if ($storage->exists($chunkDir)) {
            $storage->deleteDirectory($chunkDir);
        }

        unset($uploads[$uploadId]);
        session()->put('livewire_chunked_uploads', $uploads);
    }

    function _uploadErrored($name, $errorsInJson, $isMultiple) {
        $this->dispatch('upload:errored', name: $name)->self();

        if (! is_null($errorsInJson)) {
            $errorsInJson = $isMultiple
                ? str_ireplace('files', $name, $errorsInJson)
                : str_ireplace('files.0', $name, $errorsInJson);

            $errors = json_decode($errorsInJson, true)['errors'] ?? null;

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
        }

        $translator = app()->make('translator');

        $attribute = $translator->get("validation.attributes.{$name}");
        if ($attribute === "validation.attributes.{$name}") $attribute = $name;

        $message = trans('validation.uploaded', ['attribute' => $attribute]);
        if ($message === 'validation.uploaded') $message = "The {$name} failed to upload.";

        throw ValidationException::withMessages([$name => $message]);
    }

    function _removeUpload($name, $tmpFilename)
    {
        $uploads = $this->getPropertyValue($name);

        if (is_array($uploads) && isset($uploads[0]) && $uploads[0] instanceof TemporaryUploadedFile) {
            $this->dispatch('upload:removed', name: $name, tmpFilename: $tmpFilename)->self();

            app('livewire')->updateProperty($this, $name, array_values(array_filter($uploads, function ($upload) use ($tmpFilename) {
                if ($upload->getFilename() === $tmpFilename) {
                    $upload->delete();
                    return false;
                }

                return true;
            })));
        } elseif ($uploads instanceof TemporaryUploadedFile && $uploads->getFilename() === $tmpFilename) {
            $uploads->delete();

            $this->dispatch('upload:removed', name: $name, tmpFilename: $tmpFilename)->self();

            app('livewire')->updateProperty($this, $name, null);
        }
    }

    protected function cleanupOldUploads()
    {
        if (FileUploadConfiguration::isUsingS3()) return;

        $storage = FileUploadConfiguration::storage();

        $yesterdaysStamp = now()->subDay()->timestamp;

        foreach ($storage->allFiles(FileUploadConfiguration::path()) as $filePathname) {
            // On busy websites, this cleanup code can run in multiple threads causing part of the output
            // of allFiles() to have already been deleted by another thread.
            if (! $storage->exists($filePathname)) continue;

            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }

        // Also clean up stale chunk directories from abandoned chunked uploads.
        $chunksPath = FileUploadConfiguration::path('chunks');

        if ($storage->exists($chunksPath)) {
            foreach ($storage->directories($chunksPath) as $chunkDir) {
                $files = $storage->files($chunkDir);

                if (empty($files)) {
                    $storage->deleteDirectory($chunkDir);
                    continue;
                }

                $oldestModified = collect($files)
                    ->map(fn($f) => $storage->lastModified($f))
                    ->min();

                if ($yesterdaysStamp > $oldestModified) {
                    $storage->deleteDirectory($chunkDir);
                }
            }
        }
    }
}
