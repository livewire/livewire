<?php

namespace Livewire;

use Illuminate\Support\Facades\File;

class LivewireManager
{
    protected $components = [];

    public function register($name, $viewClass)
    {
        $this->components[$name] = $viewClass;
    }

    public function activate($name, $connection)
    {
        return new $this->components[$name]($connection, $name);
    }

    public function mock($name)
    {
        return new TestableLivewire($this->activate($name, new \StdClass));
    }

    public function script()
    {
        return '<script>'
            . File::get(__DIR__ . '/../dist/livewire.js')
            . '</script>';
    }
}
