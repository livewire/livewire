<?php

namespace Livewire\Hydration\Middleware;

interface HydrationMiddleware
{
    public static function hydrate($instance, $request);

    public static function dehydrate($instance, $response);
}
