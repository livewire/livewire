<?php

namespace Livewire;

class OnlyDuringTests
{
    public function __invoke()
    {
        $this->registerRoute();

        $this->loadAnonymousClassComponents();
    }

    public function registerRoute()
    {
    }

    public function loadAnonymousClassComponents()
    {

    }
}
