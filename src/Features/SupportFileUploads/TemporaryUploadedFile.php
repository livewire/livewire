<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class TemporaryUploadedFile extends UploadedFile
{
    protected $disk;
    protected $storage;
    protected $path;
    protected $metaFileData;

    public function __construct($path, $disk)
    {
        $this->disk = $disk;
        $this->storage = Storage::disk($this->disk);
        $this->path = FileUploadConfiguration::path($path, false);

        $tmpFile = tmpfile();

        parent::__construct(stream_get_meta_data($tmpFile)['uri'], $this->path);

        // While running tests, update the last modified timestamp to the current
        // Carbon timestamp (which respects time traveling), because otherwise
        // cleanupOldUploads() will mess up with the filesystem...
        if (app()->runningUnitTests())
        {
            @touch($this->path(), now()->timestamp);
        }
    }

    public function getPath(): string
    {
        return $this->storage->path(FileUploadConfiguration::directory());
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getSize(): int
    {
        if (app()->runningUnitTests()) {
            if (isset($this->metaFileData()['size'])) {
                return $this->metaFileData()['size'];
            }

            // This is for backwards compatibility when test file meta data was stored in the filename...
            if (str($this->getFilename())->contains('-size=')) {
                return (int) str($this->getFilename())->between('-size=', '.')->__toString();
            }
        }

        return (int) $this->storage->size($this->path);
    }

    public function getMimeType(): string
    {
        if (app()->runningUnitTests()) {
            if (isset($this->metaFileData()['type'])) {
                return $this->metaFileData()['type'];
            }

            // This is for backwards compatibility when test file meta data was stored in the filename...
            if (str($this->getFilename())->contains('-mimeType=')) {
                $escapedMimeType = str($this->getFilename())->between('-mimeType=', '-');

                // MimeTypes contain slashes, but we replaced them with underscores in `SupportTesting\Testable`
                // to ensure the filename is valid, so we now need to revert that.
                return (string) $escapedMimeType->replace('_', '/');
            }
        }

        $mimeType = $this->storage->mimeType($this->path);

        // Flysystem V2.0+ removed guess mimeType from extension support, so it has been re-added back
        // in here to ensure the correct mimeType is returned when using faked files in tests
        if (in_array($mimeType, ['application/octet-stream', 'inode/x-empty', 'application/x-empty'])) {
            $detector = new FinfoMimeTypeDetector();

            $mimeType = $detector->detectMimeTypeFromPath($this->path) ?: 'text/plain';
        }

        return $mimeType;
    }

    public function getFilename(): string
    {
        return $this->getName($this->path);
    }

    public function getRealPath(): string
    {
        return $this->storage->path($this->path);
    }

    public function getPathname(): string
    {
        return $this->getRealPath();
    }

    public function getClientOriginalName(): string
    {
        return $this->extractOriginalNameFromMetaFileData() ?? $this->extractOriginalNameFromFilePath($this->path);
    }

    public function dimensions()
    {
        stream_copy_to_stream($this->storage->readStream($this->path), $tmpFile = tmpfile());

        return @getimagesize(stream_get_meta_data($tmpFile)['uri']);
    }

    public function temporaryUrl()
    {
        if (!$this->isPreviewable()) {
            throw new FileNotPreviewableException($this);
        }

        if ((FileUploadConfiguration::isUsingS3() or FileUploadConfiguration::isUsingGCS()) && ! app()->runningUnitTests()) {
            return $this->storage->temporaryUrl(
                $this->path,
                now()->addDay()->endOfHour(),
                ['ResponseContentDisposition' => 'attachment; filename="' . urlencode($this->getClientOriginalName()) . '"']
            );
        }

        if (method_exists($this->storage->getAdapter(), 'getTemporaryUrl')) {
            // This will throw an error because it's not used with S3.
            return $this->storage->temporaryUrl($this->path, now()->addDay());
        }

        return URL::temporarySignedRoute(
            'livewire.preview-file', now()->addMinutes(30)->endOfHour(), ['filename' => $this->getFilename()]
        );
    }

    public function isPreviewable()
    {
        $supportedPreviewTypes = config('livewire.temporary_file_upload.preview_mimes', [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ]);

        return in_array($this->guessExtension(),  $supportedPreviewTypes);
    }

    public function readStream()
    {
        return $this->storage->readStream($this->path);
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

    public function storeAs($path, $name = null, $options = [])
    {
        $options = $this->parseOptions($options);

        $disk = Arr::pull($options, 'disk') ?: $this->disk;

        $newPath = trim($path.'/'.$name, '/');

        Storage::disk($disk)->put(
            $newPath, $this->storage->readStream($this->path), $options
        );

        return $newPath;
    }

    public static function generateHashName($file)
    {
        $hash = str()->random(40);
        $extension = '.'.$file->getClientOriginalExtension();

        return $hash.$extension;
    }

    public static function generateHashNameWithOriginalNameEmbedded($file)
    {
        $hash = str()->random(30);
        $meta = str('-meta'.base64_encode($file->getClientOriginalName()).'-')->replace('/', '_');
        $extension = '.'.$file->getClientOriginalExtension();

        return $hash.$meta.$extension;
    }

    public function hashName($path = null)
    {
        if (app()->runningUnitTests()) {
            if (isset($this->metaFileData()['hash'])) {
                return $this->metaFileData()['hash'];
            }

            // This is for backwards compatibility when test file meta data was stored in the filename...
            if (str($this->getFilename())->contains('-hash=')) {
                return str($this->getFilename())->between('-hash=', '-mimeType')->value();
            }
        }

        return parent::hashName($path);
    }

    public function extractOriginalNameFromFilePath($path)
    {
        return base64_decode(head(explode('-', last(explode('-meta', str($path)->replace('_', '/'))))));
    }

    public function extractOriginalNameFromMetaFileData()
    {
        return $this->metaFileData()['name'] ?? null;
    }

    public function metaFileData()
    {
        if (is_null($this->metaFileData)) {
            $this->metaFileData = [];

            if ($contents = $this->storage->get($this->path.'.json')) {
                $contents = json_decode($contents, true);

                $this->metaFileData = $contents;
            }
        }
        return $this->metaFileData;
    }

    public static function createFromLivewire($filePath)
    {
        return new static($filePath, FileUploadConfiguration::disk());
    }

    /**
     * Generate a short token for a given path using the app key and session ID.
     * This ensures tokens are unique per session and cannot be forged without the app key.
     */
    protected static function generateToken(string $path): string
    {
        return substr(hash_hmac('sha256', $path, app('encrypter')->getKey() . session()->getId()), 0, 8);
    }

    public static function signPath(string $path): string
    {
        return static::generateToken($path) . ':' . $path;
    }

    public static function extractPathFromSignedPath(string $signedPath): string|false
    {
        if (! str_contains($signedPath, ':')) {
            return false;
        }

        [$token, $path] = explode(':', $signedPath, 2);

        if (! hash_equals(static::generateToken($path), $token)) {
            return false;
        }

        return $path;
    }

    public static function canUnserialize($subject)
    {
        if (is_string($subject)) {
            return (string) str($subject)->startsWith(['livewire-file:', 'livewire-files:']);
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
            if (str($subject)->startsWith('livewire-file:')) {
                return static::createFromLivewire(str($subject)->after('livewire-file:'));
            }

            if (str($subject)->startsWith('livewire-files:')) {
                $paths = json_decode(str($subject)->after('livewire-files:'), true);

                return collect($paths)->map(function ($path) { return static::createFromLivewire($path); })->toArray();
            }
        }

        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                $subject[$key] =  static::unserializeFromLivewireRequest($value);
            }
        }

        return $subject;
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
