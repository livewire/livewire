<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Component;

class Lazy extends Component
{
    public $firstTime = true;

    public $forwards;

    public $componentName;

    public $show = false;

    public function render()
    {
        $placeholder = '';

        $instance = app('livewire')->new($this->componentName);

        if (method_exists($instance, 'placeholder')) {
            $placeholder = $instance::placeholder();
        }

        return <<<HTML
        @if (\$show)
            <livewire:dynamic-component :component="\$componentName" :apply="\$forwards" />
        @else
            <div x-intersect="\$wire.show = true; \$wire.firstTime = false; \$wire.\$commit()">
                {$placeholder}
            </div>
        @endif
        HTML;
    }
}
