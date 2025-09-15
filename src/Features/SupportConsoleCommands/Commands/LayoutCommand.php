<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:layout')]
class LayoutCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:layout {--force} {--stub= : If you have several stubs, stored in subfolders }';

    protected $description = 'Create a new app layout file';

    public function handle()
    {
        $baseViewPath = resource_path('views');

        $layout = str(config('livewire.component_layout'));

        $layoutPath = $this->layoutPath($baseViewPath, $layout);

        $relativeLayoutPath = $this->relativeLayoutPath($layoutPath);

        $force = $this->option('force');

        $stubPath = $this->stubPath($this->option('stub'));

        if (File::exists($layoutPath) && ! $force) {
            $this->line("<fg=red;options=bold>View already exists:</> {$relativeLayoutPath}");

            return false;
        }

        $this->ensureDirectoryExists($layoutPath);

        $result = File::copy($stubPath, $layoutPath);

        if ($result) {
            $this->line("<options=bold,reverse;fg=green> LAYOUT CREATED </> 🤙\n");
            $this->line("<options=bold;fg=green>CLASS:</> {$relativeLayoutPath}");
        }
    }

    protected function stubPath($stubSubDirectory = '')
    {
        $stubName = 'livewire.layout.stub';

        if (! empty($stubSubDirectory) && str($stubSubDirectory)->startsWith('..')) {
            $stubDirectory = rtrim(str($stubSubDirectory)->replaceFirst('..' . DIRECTORY_SEPARATOR, ''), DIRECTORY_SEPARATOR) . '/';
        } else {
            $stubDirectory = rtrim('stubs' . DIRECTORY_SEPARATOR . $stubSubDirectory, DIRECTORY_SEPARATOR) . '/';
        }

        if (File::exists($stubPath = base_path($stubDirectory . $stubName))) {
            return $stubPath;
        }

        return __DIR__ . DIRECTORY_SEPARATOR . $stubName;
    }

    protected function layoutPath($baseViewPath, $layout)
    {
        // Handle namespaced views like 'layouts::app'
        if ($layout->contains('::')) {
            [$namespace, $name] = $layout->explode('::');
            
            // Check if this namespace is registered in Livewire config
            $namespacePath = config("livewire.component_namespaces.{$namespace}");
            
            if ($namespacePath) {
                // Use the configured namespace path
                $baseViewPath = $namespacePath;
            } else {
                // Default to resources/views/{namespace}
                $baseViewPath = resource_path("views/{$namespace}");
            }
            
            // Now process the name part (e.g., 'app' or 'admin.dashboard')
            $directories = str($name)->explode('.');
        } else {
            // Non-namespaced view, process normally
            $directories = $layout->explode('.');
        }

        $name = Str::kebab($directories->pop());

        return $baseViewPath . DIRECTORY_SEPARATOR . collect()
            ->concat($directories)
            ->map([Str::class, 'kebab'])
            ->push("{$name}.blade.php")
            ->implode(DIRECTORY_SEPARATOR);
    }

    protected function relativeLayoutPath($layoutPath)
    {
        return (string) str($layoutPath)->replaceFirst(base_path() . '/', '');
    }
}
