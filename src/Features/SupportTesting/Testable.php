<?php

namespace Livewire\Features\SupportTesting;

use Livewire\Features\SupportFileDownloads\TestsFileDownloads;
use Livewire\Features\SupportValidation\TestsValidation;
use Livewire\Features\SupportRedirects\TestsRedirects;
use Livewire\Features\SupportEvents\TestsEvents;
use Illuminate\Support\Traits\Macroable;
use BackedEnum;

/** @mixin \Illuminate\Testing\TestResponse */

class Testable
{
    use MakesAssertions,
        TestsEvents,
        TestsRedirects,
        TestsValidation,
        TestsFileDownloads;

    use Macroable { Macroable::__call as macroCall; }

    protected function __construct(
        protected RequestBroker $requestBroker,
        protected ComponentState $lastState,
    ) {}

    /**
     * @param string $name
     * @param array $params
     * @param array $fromQueryString
     * @param array $cookies
     * @param array $headers
     *
     * @return static
     */
    static function create($name, $params = [], $fromQueryString = [], $cookies = [], $headers = [])
    {
        $name = static::normalizeAndRegisterComponentName($name);

        $requestBroker = new RequestBroker(app());

        $initialState = InitialRender::make(
            $requestBroker,
            $name,
            $params,
            $fromQueryString,
            $cookies,
            $headers,
        );

        return new static($requestBroker, $initialState);
    }

    /**
     * @param string|array<string>|object $name
     *
     * @return string
     */
    static function normalizeAndRegisterComponentName($name)
    {
        if (is_array($otherComponents = $name)) {
            $name = array_shift($otherComponents);

            foreach ($otherComponents as $key => $value) {
                if (is_numeric($key)) {
                    app('livewire')->isDiscoverable($name) || app('livewire')->component($value);
                } else {
                    app('livewire')->component($key, $value);
                }
            }
        } elseif (is_object($name)) {
            $anonymousClassComponent = $name;

            $name = str()->random(10);

            app('livewire')->component($name, $anonymousClassComponent);
        } else {
            app('livewire')->isDiscoverable($name) || app('livewire')->component($name);
        }

        return $name;
    }

    /**
     * @param ?string $driver
     *
     * @return void
     */
    static function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        auth()->guard($driver)->setUser($user);

        auth()->shouldUse($driver);
    }

    function id() {
        return $this->lastState->getComponent()->getId();
    }

    /**
     * @param string $key
     */
    function get($key)
    {
        return data_get($this->lastState->getComponent(), $key);
    }

    /**
     * @param bool $stripInitialData
     *
     * @return string
     */
    function html($stripInitialData = false)
    {
        return $this->lastState->getHtml($stripInitialData);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    function updateProperty($name, $value = null)
    {
        return $this->set($name, $value);
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    function fill($values)
    {
        foreach ($values as $name => $value) {
            $this->set($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    function toggle($name)
    {
        return $this->set($name, ! $this->get($name));
    }

    /**
     * @param string|array<string mixed> $name
     *
     * @return $this
     */
    function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->setProperty($key, $value);
            }
        } else {
            $this->setProperty($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    function setProperty($name, $value)
    {
        if ($value instanceof \Illuminate\Http\UploadedFile) {
            return $this->upload($name, [$value]);
        } elseif (is_array($value) && isset($value[0]) && $value[0] instanceof \Illuminate\Http\UploadedFile) {
            return $this->upload($name, $value, $isMultiple = true);
        } elseif ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return $this->update(updates: [$name => $value]);
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    function runAction($method, ...$params)
    {
        return $this->call($method, ...$params);
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    function call($method, ...$params)
    {
        if ($method === '$refresh') {
            return $this->commit();
        }

        if ($method === '$set') {
            return $this->set(...$params);
        }

        return $this->update(calls: [
            [
                'method' => $method,
                'params' => $params,
                'path' => '',
            ]
        ]);
    }

    /**
     * @return $this
     */
    function commit()
    {
        return $this->update();
    }

    /**
     * @return $this
     */
    function refresh()
    {
        return $this->update();
    }

    /**
     * @param array $calls
     * @param array $updates
     *
     * @return $this
     */
    function update($calls = [], $updates = [])
    {
        $newState = SubsequentRender::make(
            $this->requestBroker,
            $this->lastState,
            $calls,
            $updates,
            app('request')->cookies->all()
        );

        $this->lastState = $newState;

        return $this;
    }

    /**
     * @todo Move me outta here and into the file upload folder somehow...
     *
     * @param string $name
     * @param array $files
     * @param bool $isMultiple
     *
     * @return $this
     */
    function upload($name, $files, $isMultiple = false)
    {
        // This method simulates the calls Livewire's JavaScript
        // normally makes for file uploads.
        $this->call(
            '_startUpload',
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
            $this->call('_uploadErrored', $name, json_encode(['errors' => $e->errors()]), $isMultiple);

            return $this;
        }

        // We are going to encode the original file size, mimeType and hashName in the filename
        // so when we create a new TemporaryUploadedFile instance we can fake the
        // same file size, mimeType and hashName set for the original file upload.
        $newFileHashes = collect($files)->zip($fileHashes)->mapSpread(function ($file, $fileHash) {
            // MimeTypes contain slashes, so we replace them with underscores to ensure the filename is valid.
            $escapedMimeType = (string) str($file->getMimeType())->replace('/', '_');

            return (string) str($fileHash)->replaceFirst('.', "-hash={$file->hashName()}-mimeType={$escapedMimeType}-size={$file->getSize()}.");
        })->toArray();

        collect($fileHashes)->zip($newFileHashes)->mapSpread(function ($fileHash, $newFileHash) use ($storage) {
            $storage->move('/'.\Livewire\Features\SupportFileUploads\FileUploadConfiguration::path($fileHash), '/'.\Livewire\Features\SupportFileUploads\FileUploadConfiguration::path($newFileHash));
        });

        // Now we finish the upload with a final call to the Livewire component
        // with the temporarily uploaded file path.
        $this->call('_finishUpload', $name, $newFileHashes, $isMultiple);

        return $this;
    }

    /**
     * @param string $key
     */
    function viewData($key)
    {
        return $this->lastState->getView()->getData()[$key];
    }

    function getData()
    {
        return $this->lastState->getSnapshotData();
    }

    function instance()
    {
        return $this->lastState->getComponent();
    }

    function invade()
    {
        return \Livewire\invade($this->lastState->getComponent());
    }

    /**
     * @return $this
     */
    function dump()
    {
        dump($this->lastState->getHtml());

        return $this;
    }

    /**
     * @return void
     */
    function dd()
    {
        dd($this->lastState->getHtml());
    }

    /**
     * @return $this
     */
    function tap($callback)
    {
        $callback($this);

        return $this;
    }

    /**
     * @param string $property
     */
    function __get($property)
    {
        if ($property === 'effects') return $this->lastState->getEffects();
        if ($property === 'snapshot') return $this->lastState->getSnapshot();
        if ($property === 'target') return $this->lastState->getComponent();

        return $this->instance()->$property;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    function __call($method, $params)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        $this->lastState->getResponse()->{$method}(...$params);

        return $this;
    }
}
