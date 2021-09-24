<?php

namespace Livewire\Hydration;

use Livewire\Wireable;

class PublicPropertyManager
{
    public $publicPropertyClasses;

    public function __construct($publicPropertyClasses)
    {
           $this->publicPropertyClasses = $publicPropertyClasses ?? [];
    }

    public function register($class)
    {
        if (! $class instanceof Wireable) {
            // Throw a proper exception
        }

        $this->publicPropertyClasses[] = $class;
    }
}
