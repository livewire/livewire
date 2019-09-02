<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Livewire\LivewireComponentsFinder;
use Illuminate\Console\DetectsApplicationNamespace;

class LivewireRemoveCommand extends Command
{
    use DetectsApplicationNamespace;

    protected $signature = 'livewire:rm {name}';

    protected $description = 'Remove a Livewire component and it\'s corresponding blade view.';

    protected $parser;

    public function handle()
    {
        $this->parser = new LivewireMakeCommandParser(
            app_path(),
            head(config('view.paths')),
            $this->argument('name')
        );

        $class = $this->removeClass();
        $view = $this->removeView();

        $this->refreshComponentAutodiscovery();

        ($class && $view) && $this->line("<options=bold,reverse;fg=green> COMPONENT REMOVED </> 🤙\n");
        $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->parser->classPath()} <options=bold;fg=green>=></> 💀");
        $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->parser->viewPath()} <options=bold;fg=green>=></> 💀");
    }

    protected function removeClass()
    {
        if (! File::exists($this->parser->classPath())) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class does not exist:</> {$this->parser->relativeClassPath()}");

            return false;
        }

        return File::delete($this->parser->classPath());
    }

    protected function removeView()
    {
        if (! File::exists($this->parser->viewPath())) {
            $this->line("<fg=red;options=bold>View does not exist:</> {$this->parser->relativeViewPath()}");

            return false;
        }

        return File::delete($this->parser->viewPath());
    }

    public function refreshComponentAutodiscovery()
    {
        app(LivewireComponentsFinder::class)->build();
    }
}
