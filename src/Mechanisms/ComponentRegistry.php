<?php

namespace Livewire\Mechanisms;

use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Component;

class ComponentRegistry
{
    protected $nonAliasedClasses = [];
    protected $aliases = [];

    function boot()
    {
        app()->singleton($this::class);
    }

    function register($name, $class = null)
    {
        if (is_null($class)) {
            $this->nonAliasedClasses[] = $name;
        } else {
            $this->aliases[$name] = $class;
        }
    }

    function new($nameOrClass, $id = null)
    {
        [$class, $name] = $this->getNameAndClass($nameOrClass);

        $component = new $class;

        $component->setId($id ?: str()->random(20));

        $component->setName($name);

        return $component;
    }

    function getName($nameOrClassOrComponent)
    {
        [$class, $name] = $this->getNameAndClass($nameOrClassOrComponent);

        return $name;
    }

    protected function getNameAndClass($nameComponentOrClass)
    {
        // If a component itself was passed in, just take the class name...
        $nameOrClass = is_object($nameComponentOrClass) ? $nameComponentOrClass::class : $nameComponentOrClass;

        // If a component class was passed in, use that...
        if (class_exists($nameOrClass)) {
            $class = $nameOrClass;
        // Otherwise, assume it was a simple name...
        } else {
            $class = $this->nameToClass($nameOrClass);
        }

        // Now that we have a class, we can check that it's actually a Livewire component...
        if (! is_subclass_of($class, Component::class)) {
            throw new ComponentNotFoundException(
                "Unable to find component: [{$nameOrClass}]"
            );
        }

        // Convert it to a name even if a name was passed in to make sure we're using deterministic names...
        $name = $this->classToName($class);

        return [$class, $name];
    }

    protected function nameToClass($name)
    {
        // Check the aliases...
        if (isset($this->aliases[$name])) return $this->aliases[$name];

        // Hash check the non-aliased classes...
        foreach ($this->nonAliasedClasses as $class) {
            if (md5($class) === $name) {
                return $class;
            }
        }

        // Reverse generate a class from a name...
        return $this->generateClassFromName($name);
    }

    protected function classToName($class)
    {
        // Check the aliases...
        if ($name = array_search($class, $this->aliases)) return $name;

        // Check existance in non-aliased classes and hash...
        foreach ($this->nonAliasedClasses as $oneOff) {
            if (md5($oneOff) === $hash = md5($class)) {
                return $hash;
            }
        }

        // Generate name from class...
        return $this->generateNameFromClass($class);
    }

    protected function generateClassFromName($name)
    {
        $rootNamespace = config('livewire.class_namespace');

        $class = collect(str($name)->explode('.'))
            ->map(fn ($segment) => (string) str($segment)->studly())
            ->join('\\');

        return '\\' . $rootNamespace . '\\' . $class;
    }

    protected function generateNameFromClass($class)
    {
        $namespace = collect(explode('.', str_replace(['/', '\\'], '.', config('livewire.class_namespace'))))
            ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
            ->implode('.');

        $fullName = collect(explode('.', str_replace(['/', '\\'], '.', $class)))
            ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
            ->implode('.');

        if (str($fullName)->startsWith($namespace)) {
            return (string) str($fullName)->substr(strlen($namespace) + 1);
        }

        return $fullName;
    }



    // public function getClass($name)
    // {
    //     $subject = $name;

    //     if (isset($this->aliases[$name])) {
    //         $subject = $this->aliases[$name];
    //     }

    //     // If an anonymous object was stored in the registry,
    //     // clone its instance and return that...
    //     if (is_object($subject)) return clone $subject;

    //     // If the name or its alias are a class,
    //     // then new it up and return it...
    //     if (class_exists((string) str($subject)->studly())) {
    //         return new (str($subject)->studly()->toString());
    //     }

    //     // Otherwise, we'll look in the "autodiscovery" manifest
    //     // for the component...

    //     $getFromManifest = function ($name) {
    //         $manifest = $this->getManifest();

    //         return $manifest[$name] ?? $manifest["{$name}.index"] ?? null;
    //     };

    //     $fromManifest = $getFromManifest($subject);

    //     if ($fromManifest) return new $fromManifest;

    //     // If we couldn't find it, we'll re-generate the manifest and look again...
    //     $this->buildManifest();

    //     $fromManifest = $getFromManifest($subject);

    //     if ($fromManifest) return new $fromManifest;

    //     // By now, we give up and throw an error...
    //     throw_unless($fromManifest, new ComponentNotFoundException(
    //         "Unable to find component: [{$subject}]"
    //     ));
    // }

    // protected function write(array $manifest)
    // {
    //     if (! is_writable(dirname($this->manifestPath))) {
    //         throw new \Exception('The '.dirname($this->manifestPath).' directory must be present and writable.');
    //     }

    //     $this->files->put($this->manifestPath, '<?php return '.var_export($manifest, true).';', true);
    // }

    // public function getClassNames()
    // {
    //     if (! $this->files->exists($this->path)) {
    //         return collect();
    //     }

    //     return collect($this->files->allFiles($this->path))
    //         ->map(function (\SplFileInfo $file) {
    //             return app()->getNamespace().
    //                 str($file->getPathname())
    //                     ->after(app_path().'/')
    //                     ->replace(['/', '.php'], ['\\', ''])->__toString();
    //         })
    //         ->filter(function (string $class) {
    //             return is_subclass_of($class, Component::class) &&
    //                 ! (new \ReflectionClass($class))->isAbstract();
    //         });
    // }
}
