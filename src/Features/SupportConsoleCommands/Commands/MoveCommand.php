<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;

class MoveCommand extends FileManipulationCommand implements PromptsForMissingInput
{
    protected $signature = 'livewire:move {name} {new-name} {--force} {--inline}';

    protected $description = 'Move a Livewire component';

    protected $parser;

    protected ComponentParserFromExistingComponent $newParser;

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
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->relativeClassPath()} <options=bold;fg=green>=></> {$this->newParser->relativeClassPath()}");
        if (! $inline) $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->relativeViewPath()} <options=bold;fg=green>=></> {$this->newParser->relativeViewPath()}");
        if ($test) $test && $this->line("<options=bold;fg=green>Test:</>  {$this->parser->relativeTestPath()} <options=bold;fg=green>=></> {$this->newParser->relativeTestPath()}");
    }

    protected function renameClass()
    {
        if (File::exists($this->newParser->classPath())) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->newParser->relativeClassPath()}");

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
            $this->line("<fg=red;options=bold>View already exists:</> {$this->newParser->relativeViewPath()}");

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

        if (!File::exists($oldTestPath) || File::exists($newTestPath)) {
            return false;
        }

        $this->ensureDirectoryExists($newTestPath);

        File::put($newTestPath, $this->newParser->testContents());

        return File::delete($oldTestPath);
    }

    protected function searchComponent($value):array
    {
        $path = ComponentParser::generatePathFromNamespace(config('livewire.class_namespace'));
        return collect(File::allFiles($path))
            ->map(fn ($file) => $file->getRelativePathname())
            ->filter(fn ($file) => str($file)->contains($value, true))
            ->mapWithKeys(fn ($file, $k) => [str_replace('.php', '', $file) => str_replace('.php', '', $file)])
            ->toArray();
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn () => search(
        label: 'What is the old name of the component?',
        options: fn ($value) => strlen($value) > 0
            ? $this->searchComponent($value)
            : []
    ),
            'new-name' => 'What is the new name of the component?',
        ];
    }

    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if ($this->didReceiveOptions($input)) {
            return;
        }

        if(
            confirm(
                label: 'Is it an inline component?',
                default: false
            )
        )
        {
            $input->setOption('inline', true);
        }
    }
}
