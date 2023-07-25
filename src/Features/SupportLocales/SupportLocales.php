<?php

namespace Livewire\Features\SupportLocales;

use Livewire\ComponentHook;

class SupportLocales extends ComponentHook
{
    function hydrate($memo)
    {
        if ($locale = $memo['locale']) app()->setLocale($locale);
    }

    function dehydrate($context)
    {
        $context->addMemo('locale', app()->getLocale());
    }
}
