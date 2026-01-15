<?php

namespace Livewire\Features\SupportRouting;

class LivewirePageController
{
    public function __invoke()
    {
        $component = request()->route()->defaults['_livewire_component'];

        if ($component instanceof \Closure) {
            $component = $component();
        }

        $instance = is_object($component) && !($component instanceof \Closure)
            ? $component
            : app('livewire')->new($component);

        return app()->call([$instance, '__invoke']);
    }
}
