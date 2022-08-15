<?php

namespace Livewire\Mechanisms;

use Illuminate\Support\Facades\Blade;
use Livewire\Manager;

class BladeDirectives
{
    public function __invoke()
    {
        Blade::directive('livewireScripts', [LivewireBladeDirectives::class, 'livewireScripts']);
        Blade::directive('livewireStyles', [LivewireBladeDirectives::class, 'livewireStyles']);
        Blade::directive('livewire', [LivewireBladeDirectives::class, 'livewire']);
    }

    public static function livewireStyles($expression)
    {
        return '{!! \Livewire\Assets::styles('.$expression.') !!}';
    }

    public static function livewireScripts($expression)
    {
        return '{!! \Livewire\Assets::scripts('.$expression.') !!}';
    }

    public static function livewire($expression)
    {
        $key = "'" . str()->random(7) . "'";

        // If we are inside a Livewire component, we know we're rendering a child.
        // Therefore, we must create a more deterministic view cache key so that
        // Livewire children are properly tracked across load balancers.
        if (Manager::$currentCompilingViewPath !== null) {
            // $key = '[hash of Blade view path]-[current @livewire directive count]'
            $key = "'l" . crc32(Manager::$currentCompilingViewPath) . "-" . Manager::$currentCompilingChildCounter . "'";

            // We'll increment count, so each cache key inside a compiled view is unique.
            Manager::$currentCompilingChildCounter++;
        }

        $pattern = "/,\s*?key\(([\s\S]*)\)/"; //everything between ",key(" and ")"
        $expression = preg_replace_callback($pattern, function ($match) use (&$key) {
            $key = trim($match[1]) ?: $key;
            return "";
        }, $expression);

        return <<<EOT
<?php
\$__split = function (\$name, \$params = []) {
    return [\$name, \$params];
};
[\$__name, \$__params] = \$__split($expression);

echo \Livewire\Mechanisms\RenderComponent::mount(\$__name, \$__params, $key);

unset(\$__name);
unset(\$__params);
unset(\$__split);
?>
EOT;
    }
}
