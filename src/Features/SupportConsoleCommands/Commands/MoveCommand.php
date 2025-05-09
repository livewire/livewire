<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'livewire:move')]
class MoveCommand extends FileManipulationCommand
{
    protected $signature = 'livewire:move {name} {new-name} {--force} {--inline}';

    protected $description = 'Move a Livewire component';

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

        $inline = $this->option('inline');

        $class = $this->renameClass();
        if (! $inline) $view = $this->renameView();

        $test = $this->renameTest();

        if ($class) $this->line("<options=bold,reverse;fg=green> COMPONENT MOVED </> 🤙\n");

        $class && $this->output->writeln(
            sprintf('<options=bold;fg=green>CLASS:</> %s <options=bold;fg=green>=></> %s',
                $this->parser->relativeClassPath(),

                $this->newParser->handleFilename(
                    $this->newParser->absoluteClassPath(),
                    $this->newParser->relativeClassPath()
                )
            )
        );

        if (! $inline) $view && $this->output->writeln(
            sprintf('<options=bold;fg=green>VIEW:</> %s <options=bold;fg=green>=></> %s',
                $this->parser->relativeClassPath(),

                $this->newParser->handleFilename(
                    $this->newParser->absoluteViewPath(),
                    $this->newParser->relativeViewPath()
                )
            )
        );

        if ($test) $test && $this->output->writeln(
            sprintf('<options=bold;fg=green>TEST:</> %s <options=bold;fg=green>=></> %s',
                $this->parser->relativeTestPath(),

                $this->newParser->handleFilename(
                    $this->newParser->absoluteTestPath(),
                    $this->newParser->relativeTestPath()
                )
            )
        );
    }

    protected function renameClass()
    {
        if (File::exists($this->newParser->classPath())) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->output->writeln(
                sprintf('<fg=red;options=bold>Class already exists: </>%s',
                    $this->parser->handleFilename(
                        $this->newParser->absoluteClassPath(),
                        $this->newParser->relativeClassPath()
                    )
                )
            );

            return false;
        }

        $this->ensureDirectoryExists($this->newParser->classPath());

        File::put($this->newParser->classPath(), $this->newParser->classContents());

        return File::delete($this->parser->classPath());
    }

    protected function renameView()
    {
        $newViewPath = $this->newParser->viewPath();

        if (File::exists($newViewPath)) {
            $this->output->writeln(
                sprintf('<fg=red;options=bold>View already exists: </>%s',
                    $this->parser->handleFilename(
                        $this->newParser->absoluteViewPath(),
                        $this->newParser->relativeViewPath()
                    )
                )
            );

            return false;
        }

        $this->ensureDirectoryExists($newViewPath);

        File::move($this->parser->viewPath(), $newViewPath);

        return $newViewPath;
    }

    protected function renameTest()
    {
        $oldTestPath = $this->parser->testPath();
        $newTestPath = $this->newParser->testPath();

        if (! File::exists($oldTestPath) || File::exists($newTestPath)) {
            return false;
        }

        $this->ensureDirectoryExists($newTestPath);

        File::put($newTestPath, $this->newParser->testContents());

        File::delete($oldTestPath);

        return $newTestPath;
    }
}
