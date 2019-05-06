<?php

namespace Livewire;

use Illuminate\Support\Str;

class LivewireBladeDirectives
{
    public static function livewire($expression)
    {
        $lastArg = trim(last(explode(',', $expression)));

        if (Str::startsWith($lastArg, 'key(') && Str::endsWith($lastArg, ')')) {
            $cachedKey = Str::replaceFirst('key(', '', Str::replaceLast(')', '', $lastArg));
            $args = explode(',', $expression);
            array_pop($args);
            $expression = implode(',', $args);
        } else {
            $cachedKey = "'".str_random(7)."'";
        }

        return <<<EOT
<?php
if (! isset(\$_instance)) {
    \$dom = \Livewire\Livewire::mount({$expression})->toHtml();
} elseif (\$_instance->childHasBeenRendered($cachedKey)) {
    \$componentId = \$_instance->getRenderedChildComponentId($cachedKey);
    \$dom = \Livewire\Livewire::dummyMount(\$componentId);
    \$_instance->preserveRenderedChild($cachedKey);
} else {
    \$response = \Livewire\Livewire::mount({$expression});
    \$dom = \$response->toHtml();
    \$_instance->logRenderedChild($cachedKey, \$response->id);
}
echo \$dom;
?>
EOT;
    }
}
