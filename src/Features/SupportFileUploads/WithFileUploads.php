<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Renderless;
use Livewire\Facades\GenerateSignedUploadUrlFacade;
use Livewire\Features\SupportFormObjects\Form;

use function Livewire\invade;
use function Livewire\store;

trait WithFileUploads
{
    #[Renderless]
    function _startUpload($name, $fileInfo, $isMultiple)
    {
        $this->validateUploadBeforeTransfer($name, $fileInfo, $isMultiple);

        if (FileUploadConfiguration::isUsingS3()) {
            throw_if($isMultiple, S3DoesntSupportMultipleFileUploads::class);

            $file = UploadedFile::fake()->create($fileInfo[0]['name'], $fileInfo[0]['size'] / 1024, $fileInfo[0]['type']);

            $this->dispatch('upload:generatedSignedUrlForS3', name: $name, payload: GenerateSignedUploadUrlFacade::forS3($file))->self();

            return;
        }

        $this->dispatch('upload:generatedSignedUrl', name: $name, url: GenerateSignedUploadUrlFacade::forLocal())->self();
    }

    /**
     * Run any of the component's validation rules that can be evaluated against
     * file metadata (size, name, extension) BEFORE the file is actually transferred.
     *
     * This is a UX optimization — it lets us reject obviously-invalid uploads (e.g.
     * a 1GB file when the rule is `max:1024`) without first eating the bandwidth and
     * temp-storage cost of the full transfer. Rules that need real file contents
     * (image, dimensions, mimes, mimetypes, custom Rule objects, closures) are
     * skipped here and still run post-upload via the existing validation pass.
     *
     * The browser-supplied size/name/type are NOT trusted — server-side validation
     * still runs after the upload completes. This is purely an early-exit hint.
     */
    protected function validateUploadBeforeTransfer($name, $fileInfo, $isMultiple)
    {
        // If the upload property lives on a form object (e.g. wire:model="form.photo"),
        // walk into the form so we look up rules and messages from there instead of
        // the component. Mirrors how validateOnly() defers to form objects.
        $target = $this;
        $localName = $name;

        if (str_contains($name, '.')) {
            [$head, $rest] = explode('.', $name, 2);

            $value = $this->{$head} ?? null;

            if ($value instanceof Form) {
                $target = $value;
                $localName = $rest;
            }
        }

        $rules = $target->getRules();

        $applicable = [];
        if (isset($rules[$localName])) {
            $applicable[$localName] = $this->filterRulesForPreUploadValidation($rules[$localName]);
        }
        if ($isMultiple && isset($rules[$localName.'.*'])) {
            $applicable[$localName.'.*'] = $this->filterRulesForPreUploadValidation($rules[$localName.'.*']);
        }

        // Drop empty rule sets so the validator doesn't choke on []...
        $applicable = array_filter($applicable, fn ($r) => ! empty($r));

        if (empty($applicable)) return;

        $files = array_map(
            fn ($info) => UploadedFile::fake()->create($info['name'], (int) ($info['size'] / 1024), $info['type']),
            $fileInfo
        );

        $data = [$localName => $isMultiple ? $files : $files[0]];

        $validator = Validator::make(
            $data,
            $applicable,
            invade($target)->getMessages(),
            invade($target)->getValidationAttributes(),
        );

        if ($validator->fails()) {
            // _startUpload is marked Renderless for the happy path, but on failure
            // we need the component to re-render so the error message shows up.
            store($this)->set('skipRender', false);

            $this->dispatch('upload:errored', name: $name)->self();

            // For form-object uploads, prefix the error keys with the form
            // property name so they surface under the dot-path the developer
            // wrote on the input (e.g. `form.photo`). Mirrors the prefixing
            // Form::validateOnly() does on its own ValidationException.
            if ($target !== $this) {
                $messages = invade($validator)->messages ?: $validator->errors();
                invade($validator)->messages = new \Illuminate\Support\MessageBag(
                    \Illuminate\Support\Arr::prependKeysWith($messages->toArray(), $head.'.')
                );
                invade($validator)->failedRules = \Illuminate\Support\Arr::prependKeysWith(
                    invade($validator)->failedRules,
                    $head.'.'
                );
            }

            throw new ValidationException($validator);
        }
    }

    /**
     * Keep only the rules that are safe to evaluate against a fake UploadedFile
     * built from browser-supplied metadata. Anything else (rules that need real
     * file contents like `image`/`dimensions`/`mimes`, custom Rule objects, or
     * closures) is skipped here and falls through to the post-upload validation
     * pass on the real file contents.
     */
    protected function filterRulesForPreUploadValidation($rules)
    {
        static $safe = [
            'required', 'nullable', 'sometimes', 'present', 'filled', 'bail',
            'file', 'max', 'min', 'size', 'between', 'extensions',
        ];

        if (is_string($rules)) $rules = explode('|', $rules);
        if (! is_array($rules)) $rules = [$rules];

        return array_values(array_filter($rules, function ($rule) use ($safe) {
            if (! is_string($rule)) return false;

            return in_array(explode(':', $rule)[0], $safe, true);
        }));
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
