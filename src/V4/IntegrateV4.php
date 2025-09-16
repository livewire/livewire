<?php

namespace Livewire\V4;

use Illuminate\Support\Facades\Blade;

class IntegrateV4
{
    public function __construct()
    {
        //
    }

    public function __invoke()
    {
        $this->registerSlotDirectives();
        $this->registerSlotsSupport();
        $this->hookIntoViewClear();

        \Illuminate\Console\Application::starting(fn (\Illuminate\Console\Application $artisan) => $artisan->resolveCommands([
            \Livewire\V4\Compiler\Commands\LivewireClearCommand::class,
        ]));
    }

    protected function registerSlotDirectives()
    {
        Blade::directive('wireSlot', function ($expression) {
            return "<?php
                ob_start();
                \$__slotName = {$expression};
                // \$__slotAttributes = func_num_args() > 1 ? func_get_arg(1) : [];
                \$__previousSlotName = \$__slotName ?? null;

                // Track slot stack for nesting support
                \$__slotStack = \$__slotStack ?? [];
                array_push(\$__slotStack, \$__previousSlotName);
            ?>";
        });

        Blade::directive('endWireSlot', function () {
            return "<?php
                \$__slotContent = ob_get_clean();
                \$__slots = \$__slots ?? [];
                \$__slots[\$__slotName] = \$__slotContent;

                // Restore previous slot name from stack for nesting
                \$__slotName = array_pop(\$__slotStack);
            ?>";
        });
    }

    protected function registerSlotsSupport()
    {
        // app('livewire')->componentHook(SupportSlots::class);
    }

    protected function hookIntoViewClear()
    {
        // Hook into Laravel's view:clear command to also clear Livewire compiled files
        if (app()->runningInConsole()) {
            app('events')->listen(\Illuminate\Console\Events\CommandFinished::class, function ($event) {
                if ($event->command === 'view:clear' && $event->exitCode === 0) {
                    $this->clearLivewireCompiledFiles($event->output);
                }
            });
        }
    }

    protected function clearLivewireCompiledFiles($output = null)
    {
        try {
            $cacheDirectory = storage_path('framework/views/livewire');

            if (is_dir($cacheDirectory)) {
                // Count files before clearing for informative output
                $totalFiles = 0;
                foreach (['classes', 'views', 'scripts'] as $subdir) {
                    $path = $cacheDirectory . '/' . $subdir;
                    if (is_dir($path)) {
                        $totalFiles += count(glob($path . '/*'));
                    }
                }

                // Use the same cleanup approach as our clear command
                \Illuminate\Support\Facades\File::deleteDirectory($cacheDirectory);

                // Recreate the directory structure
                \Illuminate\Support\Facades\File::makeDirectory($cacheDirectory . '/classes', 0755, true);
                \Illuminate\Support\Facades\File::makeDirectory($cacheDirectory . '/views', 0755, true);
                \Illuminate\Support\Facades\File::makeDirectory($cacheDirectory . '/scripts', 0755, true);

                // Recreate .gitignore
                \Illuminate\Support\Facades\File::put($cacheDirectory . '/.gitignore', "*\n!.gitignore");

                // Output success message if we have access to output
                if ($output && method_exists($output, 'writeln')) {
                    if ($totalFiles > 0) {
                        $output->writeln("<info>Livewire compiled files cleared ({$totalFiles} files removed).</info>");
                    } else {
                        $output->writeln("<info>Livewire compiled files directory cleared.</info>");
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail to avoid breaking view:clear if there's an issue
            // But we can log it if output is available
            if ($output && method_exists($output, 'writeln')) {
                $output->writeln("<comment>Note: Could not clear Livewire compiled files.</comment>");
            }
        }
    }

}
