<?php

namespace Livewire\Features\SupportLocales;

use function Livewire\on;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\ComponentHook;

class SupportLocales extends ComponentHook
{
    function hydrate($meta)
    {
        if ($locale = $meta['locale']) app()->setLocale($locale);
    }

    function dehydrate($context)
    {
        $context->addMeta('locale', app()->getLocale());
    }
}
