<?php

namespace Livewire;

use Illuminate\Support\Facades\File;

class LivewireManager
{
    protected $prefix = 'livewire';
    protected $jsObject = [
        'components' => []
    ];
    protected $components = [];

    public function register($name, $viewClass)
    {
        $this->components[$name] = $viewClass;
    }

    public function activate($name)
    {
        return new $this->components[$name]($name);
    }

    public function test($name)
    {
        return new TestableLivewire($this->activate($name, new \StdClass), $this->prefix());
    }

    public function script()
    {
        return '<script>'
            . File::get(__DIR__ . '/../dist/livewire.js')
            . '</script><script>window.Livewire = '.json_encode($this->jsObject).'</script>';
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function mount($component)
    {
        $id = str_random(20);
        $instance = $this->activate($component);
        $instance->mounted();
        $dom = $instance->dom($id);
        $serialized = encrypt($instance);

        $this->jsObject['components'][$id] = [
            'id' => $id,
            'serialized' => $serialized,
            'dom' => $dom,
        ];

        return $dom;
    }
}
