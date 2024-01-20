<?php

namespace Livewire\Mechanisms;

use Illuminate\Support\Facades\Blade;

class RenderComponent extends Mechanism
{
    function boot()
    {
        Blade::directive('livewire', [static::class, 'livewire']);
    }

    public static function livewire($expression)
    {
        $key = null;

        $pattern = "/,\s*?key\(([\s\S]*)\)/"; // everything between ",key(" and ")"

        $expression = preg_replace_callback($pattern, function ($match) use (&$key) {
            $key = trim($match[1]) ?: $key;
            return "";
        }, $expression);

        if (! $key) {
            $key = app(\Livewire\Mechanisms\ExtendBlade\DeterministicBladeKeys::class)->generate();
            $key = "'{$key}'";
        }

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
