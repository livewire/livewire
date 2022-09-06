<?php

namespace Livewire\Mechanisms;

use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Drawer\IsSingleton;
use Livewire\Component;
use Livewire\Commands\ComponentParser;
use Illuminate\Filesystem\Filesystem;

class ComponentRegistry
{
    use IsSingleton;

    protected $aliases = [];
    protected $path;
    protected $files;
    protected $manifest;
    protected $manifestPath;

    function __construct()
    {
        $this->files = new Filesystem;

        // Rather than forcing users to register each individual component,
        // we will auto-detect the component's class based on its kebab-cased
        // alias. For instance: 'examples.foo' => App\Http\Livewire\Examples\Foo

        // We will generate a manifest file so we don't have to do the lookup every time.
        $defaultManifestPath = app('livewire')->isRunningServerless()
            ? '/tmp/storage/bootstrap/cache/livewire-components.php'
            : app()->bootstrapPath('cache/livewire-components.php');

        $this->manifestPath = config('livewire.manifest_path') ?: $defaultManifestPath;

        $this->path = ComponentParser::generatePathFromNamespace(
            config('livewire.class_namespace')
        );
    }

    function register($name, $class = null)
    {
        if (is_null($class)) {
            $class = $name;
            $name = $class::getName();
        }

        $this->aliases[$name] = $class;
    }

    public function get($name)
    {
        $subject = $name;

        if (isset($this->aliases[$name])) {
            $subject = $this->aliases[$name];
        }

        // If an anonymous object was stored in the registry,
        // clone its instance and return that...
        if (is_object($subject)) return clone $subject;

        // If the name or its alias are a class,
        // then new it up and return it...
        if (class_exists((string) str($subject)->studly())) {
            return new (str($subject)->studly()->toString());
        }

        // Otherwise, we'll look in the "autodiscovery" manifest
        // for the component...

        $getFromManifest = function ($name) {
            $manifest = $this->getManifest();

            return $manifest[$name] ?? $manifest["{$name}.index"] ?? null;
        };

        $fromManifest = $getFromManifest($subject);

        if ($fromManifest) return new $fromManifest;

        // If we couldn't find it, we'll re-generate the manifest and look again...
        $this->buildManifest();

        $fromManifest = $getFromManifest($subject);

        if ($fromManifest) return new $fromManifest;

        // By now, we give up and throw an error...
        throw_unless($fromManifest, new ComponentNotFoundException(
            "Unable to find component: [{$subject}]"
        ));
    }

    public function getManifest()
    {
        if (! is_null($this->manifest)) {
            return $this->manifest;
        }

        if (! file_exists($this->manifestPath)) {
            $this->buildManifest();
        }

        return $this->manifest = $this->files->getRequire($this->manifestPath);
    }

    public function buildManifest()
    {
        $this->manifest = $this->getClassNames()
            ->mapWithKeys(function ($class) {
                return [$class::getName() => $class];
            })->toArray();

        $this->write($this->manifest);

        return $this;
    }

    protected function write(array $manifest)
    {
        if (! is_writable(dirname($this->manifestPath))) {
            throw new \Exception('The '.dirname($this->manifestPath).' directory must be present and writable.');
        }

        $this->files->put($this->manifestPath, '<?php return '.var_export($manifest, true).';', true);
    }

    public function getClassNames()
    {
        if (! $this->files->exists($this->path)) {
            return collect();
        }

        return collect($this->files->allFiles($this->path))
            ->map(function (\SplFileInfo $file) {
                return app()->getNamespace().
                    str($file->getPathname())
                        ->after(app_path().'/')
                        ->replace(['/', '.php'], ['\\', ''])->__toString();
            })
            ->filter(function (string $class) {
                return is_subclass_of($class, Component::class) &&
                    ! (new \ReflectionClass($class))->isAbstract();
            });
    }
}
