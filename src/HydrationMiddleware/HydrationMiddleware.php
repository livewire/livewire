<?php

namespace Livewire\HydrationMiddleware;

interface HydrationMiddleware
{
    public function hydrate($instance, $request);

    public function dehydrate($instance, $response);
}
