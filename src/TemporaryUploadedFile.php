<?php

namespace Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class TemporaryUploadedFile extends UploadedFile
{
    protected $storage;
    protected $path;

    public function __construct($path, $disk)
    {
        $this->disk = $disk;
        $this->storage = Storage::disk($this->disk);
        $this->path = FileUploadConfiguration::path($path, false);

        $tmpFile = tmpfile();

        parent::__construct(stream_get_meta_data($tmpFile)['uri'], $this->path);
    }

    public function isValid()
    {
        return true;
    }

    public function getSize()
    {
        if (app()->environment('testing') && str::contains($this->getfilename(), '-size=')) {
            // This head/explode/last/explode nonsense is the equivelant of Str::between().
            [$beginning, $end] = ['-size=', '.'];
            return (int) head(explode($end, last(explode($beginning, $this->getFilename()))));
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

    public function getClientOriginalName()
    {
        return $this->extractOriginalNameFromFilePath($this->path);
    }

    public function temporaryUrl()
    {
        if (FileUploadConfiguration::isUsingS3() && ! app()->environment('testing')) {
            return $this->storage->temporaryUrl($this->path, now()->addDay());
        }

        $supportedPreviewTypes = [
            'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp',
            'mp4', 'mov', 'avi', 'wmv',
            'mp3', 'wav', 'm4a', 'wma',
        ];

        if (! in_array($this->guessExtension(), $supportedPreviewTypes)) {
            // This will throw an error because it's not used with S3.
            return $this->storage->temporaryUrl($this->path, now()->addDay());
        }

        return URL::temporarySignedRoute(
            'livewire.preview-file', now()->addMinutes(30), ['filename' => $this->getFilename()]
        );
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

    public function delete()
    {
        return $this->storage->delete($this->path);
    }

    public function storeAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);

        $disk = Arr::pull($options, 'disk') ?: $this->disk;

        $newPath = trim($path.'/'.$name, '/');

        if ($disk === $this->disk) {
            $this->storage->copy($this->path, $newPath);
        } else {
            Storage::disk($disk)->put(
                $newPath, $this->storage->readStream($this->path), $options
            );
        }

        return $newPath;
    }

    public static function generateHashNameWithOriginalNameEmbedded($file)
    {
        $hash = Str::random(30);
        $meta = '-meta'.base64_encode($file->getClientOriginalName()).'-';
        $extension = '.'.$file->guessExtension();

        return $hash.$meta.$extension;
    }

    public function extractOriginalNameFromFilePath($path)
    {
        return base64_decode(head(explode('-', last(explode('-meta', $path)))));
    }

    public static function createFromLivewire($filePath)
    {
        return new static($filePath, FileUploadConfiguration::disk());
    }

    public static function canUnserialize($subject)
    {
        if (is_string($subject)) {
            return Str::startsWith($subject, ['livewire-file:', 'livewire-files:']);
        } 

        if (is_array($subject)) {
            return collect($subject)->contains(function ($value) {
                return static::canUnserialize($value);
            });
        }

        return false;
    }

    public static function unserializeFromLivewireRequest($subject)
    {
        if (is_string($subject)) {
            if (Str::startsWith($subject, 'livewire-file:')) {
                return static::createFromLivewire(Str::after($subject, 'livewire-file:'));
            }

            if (Str::startsWith($subject, 'livewire-files:')) {
                $paths = json_decode(Str::after($subject, 'livewire-files:'), true);

                return collect($paths)->map(function ($path) { return static::createFromLivewire($path); })->toArray();
            }

            return $subject;
        }
        
        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                $subject[$key] =  static::unserializeFromLivewireRequest($value);
            }

            return $subject;
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
