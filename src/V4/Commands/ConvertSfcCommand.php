<?php

namespace Livewire\V4\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;

class ConvertSfcCommand extends Command
{
    protected $signature = 'livewire:convert-sfc';

    protected $description = 'Convert Volt SFC components to Livewire SFC components';

    public function handle()
    {
        $viewsDirectory = base_path('resources/views');

        $views = File::allFiles($viewsDirectory);

        foreach ($views as $view) {
            $this->convertToSfc($view);
        }
    }

    protected function convertToSfc($view)
    {
        $fileName = $view->getPathname();

        if (! str_contains($fileName, '.blade.php')) {
            return;
        }

        $content = File::get($fileName);

        if (! $this->isAVoltComponent($content)) {
            return;
        }

        note('Converting: '.$fileName);

        if (str_contains($content, 'use Livewire\Volt\Component;')) {
            $content = str_replace('use Livewire\Volt\Component;', 'use Livewire\Component;', $content);

            File::put($fileName, $content);
        }

        if (str_contains($content, 'extends \Livewire\Volt\Component')) {
            $content = str_replace('extends \Livewire\Volt\Component', 'extends \Livewire\Component', $content);

            File::put($fileName, $content);
        }

        $newFileName = str_replace('.blade.php', '.livewire.php', $fileName);

        File::move($fileName, $newFileName);

        info('Converted: '.$newFileName);
    }

    protected function isAVoltComponent($content)
    {
        return
            (
                str_contains($content, 'extends Component')
                && str_contains($content, 'use Livewire\Volt\Component;')
            )
            || str_contains($content, 'extends \Livewire\Volt\Component');
    }
}
