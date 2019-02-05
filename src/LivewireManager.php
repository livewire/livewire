<?php

namespace Livewire;

use Illuminate\Support\Facades\File;

class LivewireManager
{
    protected $prefix = 'livewire';
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
        return new TestableLivewire($this->activate($name, new \StdClass), $this->prefix());
    }

    public function script()
    {
        return '<script>'
            . File::get(__DIR__ . '/../dist/livewire.js')
            . '</script>';
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function call($component)
    {
        return <<<EOT
<div {$this->prefix()}:root="{$component}">
    <div>
        <?php
        echo "waiting...";
        ?>
    </div>
</div>
EOT;
    }
}
