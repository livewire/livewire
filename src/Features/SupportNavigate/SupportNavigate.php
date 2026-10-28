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

        Vite::useScriptTagAttributes(fn ($src, $url, $chunk, $manifest) => [
            'data-navigate-track' => static::buildVersion($manifest),
        ]);

        Vite::useStyleTagAttributes(fn ($src, $url, $chunk, $manifest) => [
            'data-navigate-track' => static::buildVersion($manifest),
        ]);
    }

    static function buildVersion($manifest)
    {
        return $manifest ? md5(json_encode($manifest)) : 'reload';
    }
}
