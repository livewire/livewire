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
        $componentClass = $this->componentsByName[$name] ?? $name;

        return new $componentClass(str_random(20), $this->prefix);
    }

    public function test($name)
    {
        return new TestableLivewire(
            LivewireComponentWrapper::wrap($this->activate($name)),
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

    public function mount($component, ...$props)
    {
        $instance = $this->activate($component);

        $wrapped = LivewireComponentWrapper::wrap($instance);
        $wrapped->created(...$props);
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
