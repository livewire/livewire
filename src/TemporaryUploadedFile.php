<?php

namespace Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TemporaryUploadedFile extends UploadedFile
{
    protected $storage;
    protected $path;

    public function __construct($path, $disk)
    {
        $this->disk = $disk;
        $this->storage = Storage::disk($this->disk);
        $this->path = 'tmp/'.$path;

        $tmpFile = tmpfile();

        parent::__construct(stream_get_meta_data($tmpFile)['uri'], $path);
    }

    public function isValid()
    {
        return true;
    }

    public function getSize()
    {
        if (app()->environment('testing') && str::contains($this->getfilename(), '-size:')) {
            return (int) str::between($this->getfilename(), '-size:', '.');
        }

        return (int) $this->storage->size($this->path);
    }

    public function getMimeType()
    {
        return $this->storage->mimeType($this->path);
    }

    public function getFilename()
    {
        return $this->getName($this->path);
    }

    public function getRealPath()
    {
        return $this->storage->path($this->path);
    }

    public function readStream()
    {
        $this->storage->readStream($this->path);
    }

    public function exists()
    {
        return $this->storage->exists($this->path);
    }

    public function get()
    {
        return $this->storage->get($this->path);
    }

    public function storeAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);

        $disk = Arr::pull($options, 'disk') ?: $this->disk;

        $newPath = trim($path.'/'.$name, '/');

        if ($disk === $this->disk) {
            return $this->storage->move($this->path, $newPath);
        }

        return Storage::disk($disk)->put(
            $newPath, $this->storage->readStream($this->path), $options
        );
    }

    public static function createFromLivewire($filePath)
    {
        $disk = app()->environment('testing')
            ? 'tmp-for-tests'
            : (config('livewire.temporary_file_upload.disk') ?: config('filsystems.default'));

        return new static($filePath, $disk);
    }

    public static function canUnserialize($subject)
    {
        return Str::startsWith($subject, 'livewire-file:')
            || Str::startsWith($subject, 'livewire-files:');
    }

    public static function unserializeFromLivewireRequest($subject)
    {
        if (Str::startsWith($subject, 'livewire-file:')) {
            return static::createFromLivewire(Str::after($subject, 'livewire-file:'));
        } elseif (Str::startsWith($subject, 'livewire-files:')) {
            $paths = json_decode(Str::after($subject, 'livewire-files:'), true);

            return collect($paths)->map(function ($path) { return static::createFromLivewire($path); })->toArray();
        }
    }

    public function serializeForLivewireResponse()
    {
        return 'livewire-file:'.$this->getFilename();
    }

    public static function serializeMultipleForLivewireResponse($files)
    {
        return 'livewire-files:'.json_encode(collect($files)->map->getFilename());
    }
}
