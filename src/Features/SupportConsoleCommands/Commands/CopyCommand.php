<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:copy')]
class CopyCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:copy {name} {new-name} {--inline} {--force} {--test}';

    protected $description = 'Copy a Livewire component';

    protected $newParser;

    public function handle()
    {
        $this->parser = new ComponentParser(
            config('livewire.class_namespace'),
            config('livewire.view_path'),
            $this->argument('name')
        );

        $this->newParser = new ComponentParserFromExistingComponent(
            config('livewire.class_namespace'),
            config('livewire.view_path'),
            $this->argument('new-name'),
            $this->parser
        );

        $force = $this->option('force');
        $inline = $this->option('inline');
        $test = $this->option('test');

        $class = $this->copyClass($force, $inline);
        if (! $inline) $view = $this->copyView($force);
        if ($test){
            $test = $this->copyTest($force);
        }
        $this->line("<options=bold,reverse;fg=green> COMPONENT COPIED </> 🤙\n");

        $class && $this->output->writeln(
            sprintf('<options=bold;fg=green>CLASS:</> %s <options=bold;fg=green>=></> %s',
                $this->parser->relativeClassPath(),

                $this->newParser->handleClickablePath(
                    $this->newParser->absoluteClassPath(),
                    $this->newParser->relativeClassPath()
                )
            )
        );

        if (! $inline) $view && $this->output->writeln(
            sprintf('<options=bold;fg=green>VIEW:</> %s <options=bold;fg=green>=></> %s',
                $this->parser->relativeViewPath(),

                $this->newParser->handleClickablePath(
                    $this->newParser->absoluteViewPath(),
                    $this->newParser->relativeViewPath()
                )
            )
        );

        if ($test) $test && $this->output->writeln(
            sprintf('<options=bold;fg=green>TEST:</> %s <options=bold;fg=green>=></> %s',
                $this->parser->relativeTestPath(),

                $this->newParser->handleClickablePath(
                    $this->newParser->absoluteTestPath(),
                    $this->newParser->relativeTestPath()
                )
            )
        );
    }

    protected function copyTest($force)
    {
        if (File::exists($this->newParser->testPath()) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");

            $this->output->writeln(
                sprintf('<fg=red;options=bold>Test class already exists: </>%s',
                    $this->newParser->handleClickablePath(
                        $this->newParser->absoluteTestPath(),
                        $this->newParser->relativeTestPath()
                    )
                )
            );

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->testPath());

        return File::copy("{$this->parser->testPath()}", $this->newParser->testPath());
    }

    protected function copyClass($force, $inline)
    {
        if (File::exists($this->newParser->classPath()) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");

            $this->output->writeln(
                sprintf('<options=bold;fg=red>Class already exists: </>%s',
                    $this->newParser->handleClickablePath(
                        $this->newParser->absoluteClassPath(),
                        $this->newParser->relativeClassPath()
                    )
                )
            );

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->classPath());

        return File::put($this->newParser->classPath(), $this->newParser->classContents($inline));
    }

    protected function copyView($force)
    {
        if (File::exists($this->newParser->viewPath()) && ! $force) {

            $this->output->writeln(
                sprintf('<fg=red;options=bold>View already exists: </>%s',
                    $this->newParser->handleClickablePath(
                        $this->newParser->absoluteViewPath(),
                        $this->newParser->relativeViewPath()
                    )
                )
            );

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->viewPath());

        return File::copy("{$this->parser->viewPath()}", $this->newParser->viewPath());
    }
}
