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
        $isDeterministic = 'false';

        $pattern = '/,\s*?key\(([\s\S]*)\)/'; // everything between ",key(" and ")"

        $expression = preg_replace_callback($pattern, function ($match) use (&$key) {
            $key = trim($match[1]) ?: $key;
            return '';
        }, $expression);

        if (! $key) {
            $key = app(\Livewire\Mechanisms\ExtendBlade\DeterministicBladeKeys::class)->generate();
            $key = "'{$key}'";
            $isDeterministic = 'true';
        }

        return <<<EOT
<?php
\$__split = function (\$name, \$params = []) {
    return [\$name, \$params];
};
[\$__name, \$__params] = \$__split($expression);

\$key = $key;

\$bufferContents = isset(\$depth) ? ob_get_contents() : null;

preg_match('/^\s*<\w+(?:[^"\'>]|"[^"]*"|\'[^\']*\')*?\s+wire:key="([^"]+)"/s', \$bufferContents, \$matches);

if (isset(\$livewireLoopCount) && isset(\$depth) && isset(\$livewireLoopCount[\$depth - 1])) {
    \$livewireLoopCount[\$depth -1]['key'] = isset(\$matches[1]) ? \$matches[1] : null;
}

if (isset(\$livewireLoopCount)) {
    \$last = \$livewireLoopCount[\$depth - 1];

    for (\$i = 0; \$i < \$depth - 1; \$i++) {
        \$key .= '-' . \$livewireLoopCount[\$i]['count'];
        if (isset(\$livewireLoopCount[\$i]['key'])) {
            \$key .= '-' . \$livewireLoopCount[\$i]['key'];
        }
    }

    \$key .= '-' . \$last['count'];
    if ($isDeterministic) {
        \$key .= '-' . (isset(\$last['key']) ? \$last['key'] : \$loop->index);
    } else {
        \$key .= '-' . $key;
    }
}

\$__html = app('livewire')->mount(\$__name, \$__params, \$key, \$__slots ?? [], get_defined_vars());

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


// What we want is something like this:
/**
 * loop - count = 1
 *
 * loop - count = 2
 *  - loop - count = 1
 *    - loop - count = 1
 *    - loop - count = 2
 *    - loop - count = 3
 *  - loop - count = 2
 *  - loop - count = 3
 *
 * loop - count = 3
 */
