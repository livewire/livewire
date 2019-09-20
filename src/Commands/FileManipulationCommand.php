<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Livewire\LivewireComponentsFinder;
use Illuminate\Console\DetectsApplicationNamespace;

class FileManipulationCommand extends Command
{
    use DetectsApplicationNamespace;

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
        $livewireFolder = app_path(collect(['Http', 'Livewire'])->implode(DIRECTORY_SEPARATOR));

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
        $this->line("\n<options=bold>Congratulations!</> 🎉🎉🎉\n");
        $this->line("You've created your first Livewire component.");
        $this->line("I've poured a ton into the Livewire experience, and I hope it shows.");
        $this->line("\nIf you dig it, here are two ways you can say thanks:");
        $this->line('⭐️  Star the repo on Github');
        $this->line('📣  Shout out the project on Twitter and tag me (@calebporzio)');
    }
}
