<?php

namespace Livewire;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

use function PHPUnit\Framework\directoryExists;

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
