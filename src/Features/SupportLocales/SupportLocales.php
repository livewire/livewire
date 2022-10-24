<?php

namespace Livewire\Features\SupportLocales;

use function Synthetic\on;
use Livewire\LivewireSynth;

class SupportLocales
{
    function boot()
    {
        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $context->addMeta('locale', app()->getLocale());
        });

        on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;

            if ($locale = $meta['locale']) {
                app()->setLocale($locale);
            }
        });
    }
}
