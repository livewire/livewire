<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Support\Facades\App;

class PersistLocale implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        if ($request['locale'] ?? null) {
            App::setLocale($request['locale']);
        }
    }

    public static function dehydrate($instance, $response)
    {
        $response->locale = App::getLocale();
    }
}
