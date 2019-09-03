<?php

namespace Livewire\Commands;

use Illuminate\Support\Facades\File;

class LivewireRenameCommand extends LivewireFileManipulationCommand
{
    protected $signature = 'livewire:mv {name} {newname}';

    protected $description = 'Create a new Livewire component and it\'s corresponding blade view.';

    public function handle()
    {
        $this->parser = new LivewireFileManipulationCommandParser(
            app_path(),
            head(config('view.paths')),
            $this->argument('name'),
            $this->argument('newname')
        );

        // dd($this->parser->newClassPath(), $this->parser->newViewPath());

        $class = $this->renameClass();
        $view = $this->renameView();

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=green> COMPONENT MOVED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->classPath()} <options=bold;fg=green>=></> {$this->parser->relativeNewClassPath()}");
        $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->viewPath()} <options=bold;fg=green>=></> {$this->parser->relativeNewViewPath()}");
    }

    protected function renameClass()
    {
        if (File::exists($this->parser->newClassPath())) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->parser->relativeNewClassPath()}");

            return false;
        }

        $this->ensureDirectoryExists($this->parser->newClassPath());

        File::put($this->parser->newClassPath(), $this->parser->newClassContents());

        return File::delete($this->parser->classPath());
    }

    protected function renameView()
    {
        $newViewPath = $this->parser->newViewPath();

        if (File::exists($newViewPath)) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->parser->relativeNewViewPath()}");

            return false;
        }

        $this->ensureDirectoryExists($newViewPath);

        File::move($this->parser->viewPath(), $newViewPath);

        return $newViewPath;
    }
}
