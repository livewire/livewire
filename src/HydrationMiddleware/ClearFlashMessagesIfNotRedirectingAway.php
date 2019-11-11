<?php

namespace Livewire\HydrationMiddleware;

class ClearFlashMessagesIfNotRedirectingAway implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        if (empty($instance->redirectTo)) {
            session()->forget(session()->get('_flash.new'));
        }
    }
}
