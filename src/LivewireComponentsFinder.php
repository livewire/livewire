<?php

namespace Livewire;

use Exception;
use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class LivewireComponentsFinder
{
    protected $path;
    protected $files;
    protected $manifest;
    protected $manifestPath;

    public function __construct(Filesystem $files, $manifestPath, $path)
    {
        $this->files = $files;
        $this->path = $path;
        $this->manifestPath = $manifestPath;
    }

    public function find($alias)
    {
        return $this->getManifest()[$alias] ?? null;
    }

    public function getManifest()
    {
        if (! is_null($this->manifest)) {
            return $this->manifest;
        }

        if (! file_exists($this->manifestPath)) {
            $this->build();
        }

        return $this->manifest = $this->files->getRequire($this->manifestPath);
    }

    public function build()
    {
        $this->manifest = $this->getClassNames()
            ->mapWithKeys(function ($class) {
                return [(new $class('dummy-id'))->getName() => $class];
            })->toArray();

        $this->write($this->manifest);

        return $this;
    }

    protected function write(array $manifest)
    {
        if (! is_writable(dirname($this->manifestPath))) {
            throw new Exception('The '.dirname($this->manifestPath).' directory must be present and writable.');
        }

        $this->files->put($this->manifestPath, '<?php return '.var_export($manifest, true).';', true);
    }

    public function getClassNames()
    {
        $composer = json_decode($this->files->get(base_path('composer.json')), true);
        $psr4Base = (array) data_get($composer, 'autoload.psr-4');

        return collect($this->files->allFiles($this->path))
            ->map(function (SplFileInfo $file) use ($psr4Base) {
                return $this->pathToPsr4Namespace(
                    Str::after($file->getPathname(), base_path().'/'),
                    $psr4Base
                );
            })
            ->filter(function (string $class) {
                return is_subclass_of($class, Component::class) &&
                    ! (new ReflectionClass($class))->isAbstract();
            });
    }

    protected function pathToPsr4Namespace(string $filename, array $psr4Base)
    {
        foreach ($psr4Base as $baseNamespace => $basePath) {
            if (Str::startsWith($filename, $basePath)) {
                $filename = $baseNamespace.Str::after($filename, $basePath);
                break;
            }
        }
        return str_replace(
            ['/', '.php'],
            ['\\', ''],
            $filename
        );
    }
}
