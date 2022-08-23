<?php

namespace Livewire\Drawer;

trait IsSingleton
{
    protected static $instance;

    private function __construct() {}

    public static function getInstance()
    {
        if (static::$instance) return static::$instance;

        return static::$instance = new static;
    }
}
