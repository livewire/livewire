<?php

namespace Livewire\Features\SupportNavigate;

use Livewire\ComponentHook;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Blade;

class SupportNavigate extends ComponentHook
{
    static function provide()
    {
        Blade::directive('persist', function ($expression) {
            return '<?php app("livewire")->forceAssetInjection(); ?><div x-persist="<?php echo e('.$expression.'); ?>">';
        });

        Blade::directive('endpersist', function ($expression) {
            return '</div>';
        });

        app('livewire')->useScriptTagAttributes([
            'data-navigate-once' => true,
        ]);

        Vite::useScriptTagAttributes([
            'data-navigate-track' => 'reload',
        ]);

        Vite::useStyleTagAttributes([
            'data-navigate-track' => 'reload',
        ]);
    }
}
