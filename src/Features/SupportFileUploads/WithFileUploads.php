<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
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
            $shouldMultipart = FileUploadConfiguration::isChunkingEnabled()
                && collect($fileInfo)->contains(
                    fn ($info) => $info['size'] > FileUploadConfiguration::chunkSizeForS3()
                );

            if ($shouldMultipart) {
                $this->dispatch('upload:generatedSignedUrlForS3Multipart',
                    name: $name,
                    config: [
                        'chunkSize' => FileUploadConfiguration::chunkSizeForS3(),
                        'retryDelays' => FileUploadConfiguration::chunkRetryDelays(),
                        'initUrl' => URL::temporarySignedRoute(
                            'livewire.s3-multipart-init',
                            now()->addMinutes(FileUploadConfiguration::chunkMaxUploadTime()),
                        ),
                    ],
                )->self();

                return;
            }

            // Single-PUT path: one presigned putObject URL per file.
            $payloads = array_map(function ($info) {
                $file = UploadedFile::fake()->create($info['name'], $info['size'] / 1024, $info['type']);

                return GenerateSignedUploadUrlFacade::forS3($file);
            }, $fileInfo);

            $this->dispatch('upload:generatedSignedUrlForS3', name: $name, payloads: $payloads)->self();

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
        $formPrefix = null;

        if (str_contains($name, '.')) {
            [$head, $rest] = explode('.', $name, 2);

            if (($value = $this->{$head} ?? null) instanceof Form) {
                $target = $value;
                $localName = $rest;
                $formPrefix = $head;
            }
        }

        $rules = $target->getRules();

        $applicable = array_filter([
            $localName => isset($rules[$localName])
                ? $this->filterRulesForPreUploadValidation($rules[$localName])
                : [],
            $localName.'.*' => $isMultiple && isset($rules[$localName.'.*'])
                ? $this->filterRulesForPreUploadValidation($rules[$localName.'.*'])
                : [],
        ]);

        if (empty($applicable)) return;

        $files = array_map(
            fn ($info) => UploadedFile::fake()->create(
                (string) ($info['name'] ?? ''),
                (int) ((is_numeric($info['size'] ?? null) ? $info['size'] : 0) / 1024),
                (string) ($info['type'] ?? ''),
            ),
            $fileInfo
        );

        $validator = Validator::make(
            [$localName => $isMultiple ? $files : $files[0]],
            $applicable,
            invade($target)->getMessages(),
            invade($target)->getValidationAttributes(),
        );

        if ($validator->passes()) return;

        // _startUpload is marked Renderless for the happy path, but on failure
        // we need the component to re-render so the error message shows up.
        store($this)->set('skipRender', false);

        $this->dispatch('upload:errored', name: $name)->self();

        // For form-object uploads, prefix the error keys with the form property
        // name so they surface under the dot-path the developer wrote on the
        // input (e.g. `form.photo`). Mirrors what Form::validateOnly() does to
        // its own ValidationException.
        if ($formPrefix !== null) {
            invade($validator)->messages = new \Illuminate\Support\MessageBag(
                \Illuminate\Support\Arr::prependKeysWith(invade($validator)->messages->toArray(), $formPrefix.'.')
            );
            invade($validator)->failedRules = \Illuminate\Support\Arr::prependKeysWith(
                invade($validator)->failedRules,
                $formPrefix.'.'
            );
        }

        throw new ValidationException($validator);
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
        if (FileUploadConfiguration::isUsingS3()) {
            $this->cleanupStaleMultipartManifests();
            return;
        }

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

    protected function cleanupStaleMultipartManifests()
    {
        $storage = FileUploadConfiguration::storage();
        $yesterdaysStamp = now()->subDay()->timestamp;
        $manifestsDir = FileUploadConfiguration::path('multipart-manifests');

        if (! $storage->exists($manifestsDir)) return;

        foreach ($storage->files($manifestsDir) as $manifestFile) {
            if (! $storage->exists($manifestFile)) continue;
            if ($storage->lastModified($manifestFile) >= $yesterdaysStamp) continue;

            $manifest = json_decode($storage->get($manifestFile), true);

            if (is_array($manifest) && isset($manifest['uploadId'], $manifest['key'])) {
                try {
                    FileUploadConfiguration::s3Client()->abortMultipartUpload([
                        'Bucket' => FileUploadConfiguration::s3Bucket(),
                        'Key' => $manifest['key'],
                        'UploadId' => $manifest['uploadId'],
                    ]);
                } catch (\Exception $e) {
                    // Already aborted/completed/expired — fine
                }
            }

            $storage->delete($manifestFile);
        }
    }
}
