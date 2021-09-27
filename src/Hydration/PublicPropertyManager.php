<?php

namespace Livewire\Hydration;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;
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
            throw new CannotRegisterPublicPropertyWithoutImplementingWireableException($class);
        }

        $this->publicPropertyClasses[] = $class;
    }
}
