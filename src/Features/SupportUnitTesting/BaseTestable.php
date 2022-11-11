<?php

namespace Livewire\Features\SupportUnitTesting;

use Livewire\Drawer\Utils;
use Illuminate\Support\Traits\Macroable;
use Livewire\Features\SupportFileUploads\FileUploadController;

use function Livewire\trigger;

class BaseTestable
{
    public $target;
    public $methods;
    public $effects;
    public $snapshot;
    public $canonical;

    use BaseMakesAssertions;

    use Macroable { __call as macroCall; }

    function __construct($dehydrated, $target) {
        $this->target = $target;
        $this->methods = $dehydrated['effects']['methods'] ?? [];
        $this->effects = $dehydrated['effects'][''] ?? [];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);
    }

    function get($key)
    {
        return data_get($this->target, $key);
    }

    function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->setProperty($key, $value);
            }

            return $this;
        }

        return $this->setProperty($name, $value);
    }

    function setProperty($key, $value)
    {
        if ($value instanceof \Illuminate\Http\UploadedFile) {
            return $this->upload($key, [$value]);
        } elseif (is_array($value) && isset($value[0]) && $value[0] instanceof \Illuminate\Http\UploadedFile) {
            return $this->upload($key, $value, $isMultiple = true);
        }

        $dehydrated = app('livewire')->update($this->snapshot, [$key => $value], $calls = []);

        // FakeRequest::get('/synthetic/update', [
        //     'targets' => [
        //         'snapshot' => $this->snapshot,
        //         'diff' => [
        //             $key => $value,
        //         ],
        //         'calls' => [],
        //     ],
        // ]));

        $this->target = $dehydrated['target'];
        $this->effects = $dehydrated['effects'][''];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);

        return $this;
    }

    /** @todo Move me outta here and into the file upload folder somehow... */
    public function upload($name, $files, $isMultiple = false)
    {
        // This methhod simulates the calls Livewire's JavaScript
        // normally makes for file uploads.
        $this->call(
            'startUpload',
            $name,
            collect($files)->map(function ($file) {
                return [
                    'name' => $file->name,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            })->toArray(),
            $isMultiple,
        );

        // This is where either the pre-signed S3 url or the regular Livewire signed
        // upload url would do its thing and return a hashed version of the uploaded
        // file in a tmp directory.
        $storage = \Livewire\Features\SupportFileUploads\FileUploadConfiguration::storage();
        try {
            $fileHashes = (new \Livewire\Features\SupportFileUploads\FileUploadController)->validateAndStore($files, \Livewire\Features\SupportFileUploads\FileUploadConfiguration::disk());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->call('uploadErrored', $name, json_encode(['errors' => $e->errors()]), $isMultiple);

            return $this;
        }

        // We are going to encode the file size in the filename so that when we create
        // a new TemporaryUploadedFile instance we can fake a specific file size.
        $newFileHashes = collect($files)->zip($fileHashes)->mapSpread(function ($file, $fileHash) {
            return (string) str($fileHash)->replaceFirst('.', "-size={$file->getSize()}.");
        })->toArray();

        collect($fileHashes)->zip($newFileHashes)->mapSpread(function ($fileHash, $newFileHash) use ($storage) {
            $storage->move('/'.\Livewire\Features\SupportFileUploads\FileUploadConfiguration::path($fileHash), '/'.\Livewire\Features\SupportFileUploads\FileUploadConfiguration::path($newFileHash));
        });

        // Now we finish the upload with a final call to the Livewire component
        // with the temporarily uploaded file path.
        $this->call('finishUpload', $name, $newFileHashes, $isMultiple);

        return $this;
    }

    function commit()
    {
        $dehydrated = app('livewire')->update($this->snapshot, $diff = [], $calls = []);

        $this->target = $dehydrated['target'];
        $this->effects = $dehydrated['effects'][''];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);

        return $this;
    }

    function runAction($method, ...$params)
    {
        return $this->call($method, ...$params);
    }

    function call($method, ...$params)
    {
        $dehydrated = app('livewire')->update($this->snapshot, $diff = [], $calls = [[
            'method' => $method,
            'params' => $params,
            'path' => '',
        ]]);

        $this->target = $dehydrated['target'];
        $this->effects = $dehydrated['effects'][''];
        $this->snapshot = $dehydrated['snapshot'];
        $this->canonical = $this->extractData($this->snapshot['data']);

        return $this;
    }

    function extractData($payload) {
        $value = Utils::isSyntheticTuple($payload) ? $payload[0] : $payload;

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $value[$key] = $this->extractData($child);
            }
        }

        return $value;
    }

    function __get($property)
    {
        return $this->target->$property;
    }

    function __set($property, $value)
    {
        throw new \Exception('Properties of this object are "readonly"');
    }
}
