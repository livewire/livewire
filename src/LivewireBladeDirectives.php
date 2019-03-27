<?php

namespace Livewire;

class LivewireBladeDirectives
{
    public static function livewire($expression)
    {
        return <<<EOT
<?php
list(\$dom, \$id, \$serialized) = \Livewire\Livewire::mount({$expression});

echo \Livewire\Livewire::injectComponentDataAsHtmlAttributesInRootElement(\$dom, \$id, \$serialized);
?>
EOT;
    }
}
