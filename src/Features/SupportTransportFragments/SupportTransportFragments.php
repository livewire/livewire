<?php

namespace Livewire\Features\SupportTransportFragments;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportTransportFragments extends ComponentHook
{
    public static function provide(): void
    {
        Blade::directive('fragment', function ($expression) {
            $expression = trim($expression) ?: 'null';

            return "<?php \$__livewireTransportFragmentName = {$expression}; if (isset(\$__livewire) && config('livewire.update_engine') === 'delta') echo \$__livewire->startTransportFragment(\$__livewireTransportFragmentName); \$__env->startFragment(\$__livewireTransportFragmentName); unset(\$__livewireTransportFragmentName); ?>";
        });

        Blade::directive('endfragment', function () {
            return "<?php \$__livewireTransportFragmentEnd = isset(\$__livewire) && config('livewire.update_engine') === 'delta' ? \$__livewire->endTransportFragment() : ''; echo \$__env->stopFragment(); echo \$__livewireTransportFragmentEnd; unset(\$__livewireTransportFragmentEnd); ?>";
        });
    }

    public function render(): \Closure
    {
        $this->component->resetTransportFragments();

        return function () {
            $this->component->ensureTransportFragmentsAreClosed();
        };
    }
}
