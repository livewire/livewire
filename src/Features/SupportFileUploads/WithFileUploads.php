<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;

trait WithFileUploads
{
    #[Renderless]
    function _startUpload($name, $fileInfo, $isMultiple)
    {
        $plan = app(UploadPlanner::class)->plan(
            $fileInfo, $isMultiple, DeclaredSizeRules::for($this, $name, $isMultiple)
        );

        $this->dispatch('upload:plan', name: $name, plan: $plan)->self();
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

        $uploads = collect($tmpPath)->map(function ($i) {
            return TemporaryUploadedFile::createFromLivewire($i);
        });

        if ($isMultiple) {
            $file = $uploads->toArray();

            if ($append) {
                $existing = $this->getPropertyValue($name);
                if ($existing instanceof \Illuminate\Support\Collection) {
                    $file = $existing->merge($file);
                } elseif (is_array($existing)) {
                    $file = array_merge($existing, $file);
                }
            }
        } else {
            $file = $uploads->first();

            // If the property is an array, but the upload ISNT set to "multiple"
            // then APPEND the upload to the array, rather than replacing it.
            if (is_array($value = $this->getPropertyValue($name))) {
                $file = array_merge($value, [$file]);
            }
        }

        // A file that fails the property's validation rules should never be
        // attached to the component — reject it before announcing success...
        $this->validateIncomingUpload($name, $file, $uploads);

        $this->dispatch('upload:finished', name: $name, tmpFilenames: $uploads->map->getFilename()->toArray())->self();

        app('livewire')->updateProperty($this, $name, $file);
    }

    protected function validateIncomingUpload($name, $candidate, $uploads)
    {
        // Rules from #[Validate] on form object properties are registered on
        // the form object itself, not the root component...
        $target = $this;
        $field = $name;
        $root = (string) str($name)->before('.');

        if (($this->all()[$root] ?? null) instanceof \Livewire\Features\SupportFormObjects\Form) {
            $target = $this->all()[$root];
            $field = (string) str($name)->after('.');
        }

        if ($target->missingRuleFor($field)) return;

        try {
            if (is_array($candidate) || $candidate instanceof \Illuminate\Support\Collection) {
                // Validate each incoming file against the property's wildcard
                // rules — files already attached passed when they arrived...
                $total = count($candidate);

                for ($i = $total - $uploads->count(); $i < $total; $i++) {
                    $target->validateOnly("{$field}.{$i}", dataOverrides: [$field => $candidate]);
                }
            } else {
                $target->validateOnly($field, dataOverrides: [$field => $candidate]);
            }
        } catch (ValidationException $e) {
            $uploads->each->delete();

            $this->dispatch('upload:errored', name: $name)->self();

            throw $e;
        }
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

        foreach ($storage->allFiles(FileUploadConfiguration::path()) as $filePathname) {
            // On busy websites, this cleanup code can run in multiple threads causing part of the output
            // of allFiles() to have already been deleted by another thread.
            if (! $storage->exists($filePathname)) continue;

            $yesterdaysStamp = now()->subDay()->timestamp;
            if ($yesterdaysStamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }

        // Remove chunk directories whose chunks have all been cleaned up above...
        foreach ($storage->directories(FileUploadConfiguration::path('chunks')) as $directory) {
            if (empty($storage->allFiles($directory))) {
                $storage->deleteDirectory($directory);
            }
        }
    }
}
