<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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

    public function isFirstTimeMakingAComponent()
    {
        $namespace = str(config('livewire.class_namespace'))->replaceFirst(app()->getNamespace(), '');

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
    }
}
