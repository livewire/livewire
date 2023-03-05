<?php

namespace Livewire\Mechanisms;

use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Drawer\ImplicitlyBoundMethod;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Manager;
use Throwable;

use function Livewire\on;
use function Livewire\trigger;
use function Livewire\store;
use function Livewire\wrap;

class RenderComponent
{
    function boot()
    {
        app()->singleton($this::class);

        Blade::directive('livewire', [static::class, 'livewire']);
    }

    public static function livewire($expression)
    {
        $key = "'" . Str::random(7) . "'";

        $pattern = "/,\s*?key\(([\s\S]*)\)/"; //everything between ",key(" and ")"

        $expression = preg_replace_callback($pattern, function ($match) use (&$key) {
            $key = trim($match[1]) ?: $key;
            return "";
        }, $expression);

        // If we are inside a Livewire component, we know we're rendering a child.
        // Therefore, we must create a more deterministic view cache key so that
        // Livewire children are properly tracked across load balancers.
        // if (Manager::$currentCompilingViewPath !== null) {
        //     // $key = '[hash of Blade view path]-[current @livewire directive count]'
        //     $key = "'l" . crc32(Manager::$currentCompilingViewPath) . "-" . Manager::$currentCompilingChildCounter . "'";

        //     // We'll increment count, so each cache key inside a compiled view is unique.
        //     Manager::$currentCompilingChildCounter++;
        // }

        return <<<EOT
<?php
\$__split = function (\$name, \$params = []) {
    return [\$name, \$params];
};
[\$__name, \$__params] = \$__split($expression);

\$__html = app('livewire')->mount(\$__name, \$__params, $key, \$__slots ?? [], get_defined_vars());

echo \$__html;

unset(\$__html);
unset(\$__name);
unset(\$__params);
unset(\$__split);
if (isset(\$__slots)) unset(\$__slots);
?>
EOT;
    }
}
