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

        $pattern = '/,\s*?key\(([\s\S]*)\)/'; // everything between ",key(" and ")"

        $expression = preg_replace_callback($pattern, function ($match) use (&$key) {
            $key = trim($match[1]) ?: $key;
            return '';
        }, $expression);

        if (is_null($key)) {
            $key = 'null';    
        }

        $deterministicBladeKey = app(\Livewire\Mechanisms\ExtendBlade\DeterministicBladeKeys::class)->generate();
        $deterministicBladeKey = "'{$deterministicBladeKey}'";

        return <<<EOT
<?php
\$__split = function (\$name, \$params = []) {
    return [\$name, \$params];
};
[\$__name, \$__params] = \$__split($expression);

\$__key = $key;

\$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey($deterministicBladeKey, \$__key);

\$__html = app('livewire')->mount(\$__name, \$__params, \$__key);

echo \$__html;

unset(\$__html);
unset(\$__key);
unset(\$__name);
unset(\$__params);
unset(\$__split);
if (isset(\$__slots)) unset(\$__slots);
?>
EOT;
    }
}
