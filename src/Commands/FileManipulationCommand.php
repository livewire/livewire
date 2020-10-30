<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Livewire\LivewireComponentsFinder;
use function Livewire\str;

class FileManipulationCommand extends Command
{
    protected $parser;

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    public function refreshComponentAutodiscovery()
    {
        app(LivewireComponentsFinder::class)->build();
    }

    public function isFirstTimeMakingAComponent()
    {
        $namespace = str(config('livewire.class_namespace', 'App\\Http\\Livewire'))->replaceFirst(app()->getNamespace(), '');

        $livewireFolder = app_path($namespace->explode('\\')->implode(DIRECTORY_SEPARATOR));

        return ! File::isDirectory($livewireFolder);
    }

    public function writeWelcomeMessage()
    {
        $asciiLogo = <<<EOT
<fg=magenta>  _._</>
<fg=magenta>/ /<fg=white>o</>\ \ </> <fg=cyan> || ()                ()  __         </>
<fg=magenta>|_\ /_|</>  <fg=cyan> || || \\\// /_\ \\\ // || |~~ /_\   </>
<fg=magenta> <fg=cyan>|</>`<fg=cyan>|</>`<fg=cyan>|</> </>  <fg=cyan> || ||  \/  \\\_  \^/  || ||  \\\_   </>
EOT;
//     _._
        //   / /o\ \   || ()                ()  __
        //   |_\ /_|   || || \\\// /_\ \\\ // || |~~ /_\
//    |`|`|    || ||  \/  \\\_  \^/  || ||  \\\_
        $this->line("\n".$asciiLogo."\n");
        $this->line("\n<options=bold>Congratulations, you've created your first Livewire component!</> 🎉🎉🎉\n");
        if ($this->confirm('Would you like to show some love by starring the repo?')) {
            if(PHP_OS_FAMILY == 'Darwin') exec('open https://github.com/livewire/livewire');
            if(PHP_OS_FAMILY == 'Windows') exec('start https://github.com/livewire/livewire');
            if(PHP_OS_FAMILY == 'Linux') exec('xdg-open https://github.com/livewire/livewire');

            $this->line("Thanks! Means the world to me!");
        } else {
            $this->line("I understand, but am not going to pretend I'm not sad about it...");
        }
    }
}
