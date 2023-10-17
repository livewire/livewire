<?php

namespace Livewire\Features\SupportNavigate;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Livewire\ComponentHook;

class SupportNavigate extends ComponentHook
{
    public static function provide()
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
