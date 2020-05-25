<?php

namespace Livewire\Testing\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Controllers\FileUploadHandler;

trait MakesCallsToComponent
{
    public function emit($event, ...$parameters)
    {
        return $this->fireEvent($event, $parameters);
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

    public function updateProperty($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->sendMessage('syncInput', [
                    'name' => $key,
                    'value' => $value,
                ]);
            }

            return $this;
        }

        if ($value instanceof UploadedFile) {
            $this->sendMessage('callMethod', [
                'method' => 'generateSignedRoute',
                'params' => [$name, [[
                    'name' => $value->name,
                    'size' => $value->getSize(),
                    'type' => $value->getMimeType(),
                ]]],
            ], false);

            // This is where either the pre-signed S3 url or the regular Livewire signed
            // upload url would do its thing and return a hashed version of the uploaded
            // file in a tmp directory.
            Storage::fake($disk = 'tmp-for-tests');
            $fileHash = (new FileUploadHandler)->validateAndStore([$value], $disk)[0];

            $this->sendMessage('callMethod', [
                'method' => 'finishUpload',
                'params' => [$name, [$fileHash]],
            ], false);

            return $this;
        }

        $this->sendMessage('syncInput', [
            'name' => $name,
            'value' => $value,
        ]);

        return $this;
    }

    public function sendMessage($message, $payload)
    {
        $this->lastResponse = $this->pretendWereSendingAComponentUpdateRequest($message, $payload);

        if (! $this->lastResponse->exception) {
            $this->updateComponent($this->lastResponse->original);
        }
    }
}
