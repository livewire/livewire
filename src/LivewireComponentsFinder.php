<?php

namespace Livewire;

use Exception;
use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class LivewireComponentsFinder
{
    protected $files;
    protected $path;
    protected $manifestPath;
    protected $manifest;

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

        return $this->manifest =  $this->files->getRequire($this->manifestPath);
    }

    public function build()
    {
        $this->manifest = $this->getClassNames()
            ->mapWithKeys(function ($class) {
                return [(new $class)->name() => $class];
            })->toArray();

        $this->write($this->manifest);
    }

    protected function write(array $manifest)
    {
        if (! is_writable(dirname($this->manifestPath))) {
            throw new Exception('The '.dirname($this->manifestPath).' directory must be present and writable.');
        }

        $this->files->replace(
            $this->manifestPath, '<?php return '.var_export($manifest, true).';'
        );
    }

    public function getClassNames()
    {
        return collect($this->files->allFiles($this->path))
            ->map(function (SplFileInfo $file) {
                return app()->getNamespace().str_replace(
                        ['/', '.php'],
                        ['\\', ''],
                        Str::after($file->getPathname(), app_path().DIRECTORY_SEPARATOR)
                    );
            })
            ->filter(function (string $class) {
                return is_subclass_of($class, Component::class) &&
                    ! (new ReflectionClass($class))->isAbstract();
            });
    }
}
