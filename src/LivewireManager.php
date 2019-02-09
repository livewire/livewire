<?php

namespace Livewire;

use Illuminate\Support\Facades\File;

class LivewireManager
{
    protected $prefix = 'wire';
    protected $componentsByName = [];
    protected $jsObject = [
        'componentsById' => []
    ];

    public function register($name, $viewClass)
    {
        $this->componentsByName[$name] = $viewClass;
    }

    public function activate($name)
    {
        throw_unless(
            isset($this->componentsByName[$name]),
            new \Exception('Livewire component not registered: [' . $name . ']')
        );

        return new $this->componentsByName[$name](str_random(20), $this->prefix);
    }

    public function test($name)
    {
        return new TestableLivewire(
            $this->activate($name),
            $this->prefix()
        );
    }

    public function script()
    {
        return '<script>'
            . File::get(__DIR__ . '/../dist/livewire.js')
            . '</script>'
            . '<script>window.Livewire = '.json_encode($this->jsObject).'</script>';
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
        $instance = $this->activate($component);
        $instance->created();
        $dom = $instance->output();
        $instance->mounted();
        $serialized = encrypt($instance);

        $this->jsObject['componentsById'][$id] = [
            'id' => $instance->id,
            'serialized' => $serialized,
            'dom' => $dom,
        ];

        return $dom;
    }
}
