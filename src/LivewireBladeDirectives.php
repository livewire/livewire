<?php

namespace Livewire;

class LivewireBladeDirectives
{
    public static function livewire($expression)
    {
        $id = str_random(7);

        return <<<EOT
<?php
if (! isset(\$_instance)) {
    \$dom = \Livewire\Livewire::mount({$expression})->dom;
} elseif (\$_instance->childHasBeenRendered('$id')) {
    \$componentId = \$_instance->getRenderedChildComponentId('$id');
    \$dom = \Livewire\Livewire::dummyMount(\$componentId);
} else {
    \$output = \Livewire\Livewire::mount({$expression});
    \$dom = \$output->dom;
    \$_instance->logRenderedChild('$id', \$output->id);
}
echo \$dom;
?>
EOT;
    }
}
