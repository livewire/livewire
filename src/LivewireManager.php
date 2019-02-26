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
    protected $isTesting = false;

    public function register($name, $viewClass)
    {
        $this->componentsByName[$name] = $viewClass;
    }

    public function getComponentClass($component)
    {
        return $this->componentsByName[$component] ?? $component;
    }

    public function activate($name)
    {
        $componentClass = $this->getComponentClass($name);

        return new $componentClass(str_random(20), $this->prefix);
    }

    public function test($name)
    {
        $this->isTesting = true;

        return new TestableLivewire($name, $this->prefix);
    }

    public function script()
    {
        return '<script>'
            . File::get(__DIR__ . '/../dist/livewire.js')
            . '</script>'
            . '<script>Livewire.start()</script>';
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function mount($component, ...$options)
    {
        $instance = $this->activate($component);

        if ($this->isTesting) {
            $wrapped = TestableLivewireComponentWrapper::wrap($instance);
        } else {
            $wrapped = LivewireComponentWrapper::wrap($instance);
        }

        $wrapped->created(...$options);
        $dom = $wrapped->output();
        $wrapped->mounted();
        $serialized = encrypt($instance);

        return [$dom, $instance->id, $serialized];
    }

    public function injectDataForJsInComponentRootAttributes($dom, $id, $serialized)
    {
        return preg_replace(
            '/(<[a-zA-Z0-9\-]*)/',
            sprintf('$1 %s:root-id="%s" id="%s" %s:root-serialized="%s"', $this->prefix, $id, $id, $this->prefix, $serialized),
            $dom,
            $limit = 1
        );
    }

    public function layout($layout)
    {
        return (new LivewireRouteDefinition())->layout($layout);
    }

    public function section($section)
    {
        return (new LivewireRouteDefinition())->section($section);
    }

    public function get($uri, $component, $options = [])
    {
        return (new LivewireRouteDefinition())->get($uri, $component);
    }
}
