<?php

namespace Livewire\Testing\Concerns;

use function Livewire\str;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Livewire\FileUploadConfiguration;
use Livewire\Controllers\FileUploadHandler;
use Illuminate\Validation\ValidationException;

trait MakesCallsToComponent
{
    public function emit($event, ...$parameters)
    {
        return $this->fireEvent($event, ...$parameters);
    }

    public function fireEvent($event, ...$parameters)
    {
        $this->sendMessage('fireEvent', [
            'event' => $event,
            'params' => $parameters,
        ]);

        return $this;
    }

    public function call($method, ...$parameters)
    {
        return $this->runAction($method, ...$parameters);
    }

    public function runAction($method, ...$parameters)
    {
        $this->sendMessage('callMethod', [
            'method' => $method,
            'params' => $parameters,
            'ref' => null,
        ]);

        return $this;
    }

    public function fill($values)
    {
        foreach ($values as $name => $value) {
            $this->set($name, $value);
        }

        return $this;
    }

    public function set($name, $value = null)
    {
        return $this->updateProperty($name, $value);
    }

    public function toggle($name)
    {
        return $this->set($name, ! $this->get($name));
    }

    public function updateProperty($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->syncInput($key, $value);
            }

            return $this;
        }

        return $this->syncInput($name, $value);
    }

    public function syncInput($name, $value)
    {
        if ($value instanceof UploadedFile) {
            return $this->syncUploadedFiles($name, [$value]);
        } elseif (is_array($value) && isset($value[0]) && $value[0] instanceof UploadedFile) {
            return $this->syncUploadedFiles($name, $value, $isMultiple = true);
        }

        return $this->sendMessage('syncInput', [
            'name' => $name,
            'value' => $value,
        ]);
    }

    public function syncUploadedFiles($name, $files, $isMultiple = false)
    {
        // This methhod simulates the calls Livewire's JavaScript
        // normally makes for file uploads.
        $this->sendMessage('callMethod', [
            'method' => 'startUpload',
            'params' => [$name, collect($files)->map(function ($file) {
                return [
                    'name' => $file->name,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            })->toArray(), $isMultiple],
        ]);

        // This is where either the pre-signed S3 url or the regular Livewire signed
        // upload url would do its thing and return a hashed version of the uploaded
        // file in a tmp directory.
        $storage = FileUploadConfiguration::storage();
        try {
            $fileHashes = (new FileUploadHandler)->validateAndStore($files, FileUploadConfiguration::disk());
        } catch (ValidationException $e) {
            $this->runAction('uploadErrored', $name, json_encode(['errors' => $e->errors()]), $isMultiple);

            return $this;
        }

        // We are going to encode the file size in the filename so that when we create
        // a new TemporaryUploadedFile instance we can fake a specific file size.
        $newFileHashes = collect($files)->zip($fileHashes)->mapSpread(function ($file, $fileHash) {
            return (string) str($fileHash)->replaceFirst('.', "-size={$file->getSize()}.");
        })->toArray();

        collect($fileHashes)->zip($newFileHashes)->mapSpread(function ($fileHash, $newFileHash) use ($storage) {
            $storage->move('/'.FileUploadConfiguration::path($fileHash), '/'.FileUploadConfiguration::path($newFileHash));
        });

        // Now we finish the upload with a final call to the Livewire component
        // with the temporarily uploaded file path.
        $this->sendMessage('callMethod', [
            'method' => 'finishUpload',
            'params' => [$name, $newFileHashes, $isMultiple],
        ]);

        return $this;
    }

    public function sendMessage($message, $payload)
    {
        $payload['id'] = Str::random(4);

        $this->lastResponse = $this->pretendWereSendingAComponentUpdateRequest($message, $payload);

        if (! $this->lastResponse->exception) {
            $this->updateComponent($this->lastResponse->original);
        }

        return $this;
    }
}
