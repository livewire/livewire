<?php

namespace Livewire;

use Illuminate\Support\Facades\File;
use Livewire\Testing\TestableLivewire;
use Livewire\Testing\TestableLivewireComponentWrapper;

class LivewireManager
{
    protected $prefix = 'wire';
    protected $componentAliases = [];
    protected $isTesting = false;

    public function prefix($prefix = null)
    {
        // Yes, this is both a getter and a setter. Fight me.
        return $this->prefix = $prefix ?: $this->prefix;
    }

    public function component($alias, $viewClass)
    {
        $this->componentAliases[$alias] = $viewClass;
    }

    public function getComponentClass($aliasOrClass)
    {
        return $this->componentAliases[$aliasOrClass] ?? $aliasOrClass;
    }

    public function activate($componentAliasOrClass)
    {
        $componentClass = $this->getComponentClass($componentAliasOrClass);

        return new $componentClass(str_random(20), $this->prefix);
    }

    public function scripts($options = [])
    {
        return '<script>'
            . File::get(__DIR__ . '/../dist/livewire.js')
            . 'window.livewire = new Livewire('.json_encode($options).');'
            . 'window.livewire_token = "'.csrf_token().'";'
            . '</script>';
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

    public function injectComponentDataAsHtmlAttributesInRootElement($dom, $id, $serialized)
    {
        return preg_replace(
            '/(<[a-zA-Z0-9\-]*)/',
            sprintf('$1 %s:id="%s" id="%s" %s:serialized="%s"', $this->prefix, $id, $id, $this->prefix, $serialized),
            $dom,
            $limit = 1
        );
    }

    public function test($name)
    {
        $this->isTesting = true;

        return new TestableLivewire($name, $this->prefix);
    }
}
