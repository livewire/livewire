<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Renderless;
use Livewire\Facades\GenerateSignedUploadUrlFacade;

trait WithFileUploads
{
    #[Renderless]
    function _startUpload($name, $fileInfo, $isMultiple)
    {
        if (FileUploadConfiguration::isUsingS3()) {
            throw_if($isMultiple, S3DoesntSupportMultipleFileUploads::class);
            throw_if(FileUploadConfiguration::isChunkingEnabled(), S3DoesntSupportChunkedUploads::class);

            $file = UploadedFile::fake()->create($fileInfo[0]['name'], $fileInfo[0]['size'] / 1024, $fileInfo[0]['type']);

            $this->dispatch('upload:generatedSignedUrlForS3', name: $name, payload: GenerateSignedUploadUrlFacade::forS3($file))->self();

            return;
        }

        // Determine if any of the files in this upload should be chunked.
        // We chunk if chunking is enabled AND any individual file is larger
        // than the configured chunk size.
        $shouldChunk = FileUploadConfiguration::isChunkingEnabled()
            && collect($fileInfo)->contains(fn ($info) => $info['size'] > FileUploadConfiguration::chunkSize());

        $chunkConfig = null;
        if ($shouldChunk) {
            $chunkConfig = [
                'chunkSize' => FileUploadConfiguration::chunkSize(),
                'retryDelays' => FileUploadConfiguration::chunkRetryDelays(),
                'initUrl' => URL::temporarySignedRoute(
                    'livewire.chunk-upload-init',
                    now()->addMinutes(FileUploadConfiguration::chunkMaxUploadTime()),
                ),
            ];
        }

        $this->dispatch('upload:generatedSignedUrl', name: $name, url: GenerateSignedUploadUrlFacade::forLocal(), chunkConfig: $chunkConfig)->self();
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
        $tmpDir = FileUploadConfiguration::path();

        foreach ($storage->files($tmpDir) as $filePathname) {
            // On busy websites, this cleanup code can run in multiple threads causing part of the output
            // of files() to have already been deleted by another thread.
            if (! $storage->exists($filePathname)) continue;

            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }

        // Clean up stale chunk directories. Each chunk directory contains a
        // manifest.json that gets updated with each PATCH, so we use the
        // manifest's mtime to detect abandoned uploads.
        $chunksDir = FileUploadConfiguration::path('chunks');

        if ($storage->exists($chunksDir)) {
            foreach ($storage->directories($chunksDir) as $dir) {
                $manifestPath = "{$dir}/manifest.json";

                if (! $storage->exists($manifestPath)) {
                    // Orphaned chunk directory with no manifest. Only delete if
                    // the directory itself is also stale to avoid racing against
                    // an in-flight init that hasn't written the manifest yet.
                    $files = $storage->allFiles($dir);
                    $allOld = true;
                    foreach ($files as $f) {
                        if ($storage->lastModified($f) > $yesterdaysStamp) {
                            $allOld = false;
                            break;
                        }
                    }
                    if ($allOld) $storage->deleteDirectory($dir);
                    continue;
                }

                if ($yesterdaysStamp > $storage->lastModified($manifestPath)) {
                    $storage->deleteDirectory($dir);
                }
            }
        }
    }
}
