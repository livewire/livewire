<?php

namespace Livewire;

class LivewireBladeDirectives
{
    public static function livewire($expression)
    {
        // This "internalKey" is a key that will only be used on the server-side.
        // It is set outside the string, so that it is stored as a literal in the
        // compiled view which will be persisted across multiple ajax requests.
        // When a view is updated, this key will change, forcing the server to mount
        // children that would otherwise just be stubbed out. I think this is fine,
        // because if a user updates a view, they would want a re-mount anyways?
        $internalKey = str_random(20);

        return <<<EOT
<?php
list(\$dom, \$id, \$serialized) = isset(\$wrapped)
    ? \$wrapped->mountChild('{$internalKey}', {$expression})
    : \Livewire\Livewire::mount({$expression});

if (isset(\$wrapped)) {
    \$wrapped->setCurrentChildInView(\$id);
}

echo \Livewire\Livewire::injectDataForJsInComponentRootAttributes(\$dom, \$id, \$serialized);
?>
EOT;
    }

    public static function on($expression)
    {
        return <<<EOT
<?php if (isset(\$wrapped)) { \$wrapped->prepareListenerForRegistration({$expression}); } ?>
EOT;
    }

    public static function endlivewire($expression)
    {
        return <<<EOT
<?php if (isset(\$wrapped)) { \$wrapped->registerListeners(); } ?>
EOT;
    }
}
