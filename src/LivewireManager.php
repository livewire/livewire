<?php

namespace Livewire;

use Illuminate\Support\Facades\File;
use Livewire\Testing\TestableLivewire;
use Livewire\Connection\ComponentHydrator;

class LivewireManager
{
    protected $prefix = 'wire';
    protected $componentAliases = [];

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

        return new $componentClass(str_random(20));
    }

    public function scripts($options = null)
    {
        $options = $options ? json_encode($options) : '';
        $jsInclude = File::get(__DIR__ . '/../dist/livewire.js');
        $csrf = csrf_token();

        return <<<EOT
<script>
    {$jsInclude}
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        window.livewire = new Livewire({$options});
        window.livewire_token = "{$csrf}";
    });
</script>
EOT;
    }

    public function mount($component, ...$options)
    {
        $instance = $this->activate($component);

        $instance->created(...$options);
        $dom = $instance->output();
        $serialized = ComponentHydrator::dehydrate($instance);

        return new LivewireOutput([
            'id' => $instance->id,
            'dom' => $this->injectComponentDataAsHtmlAttributesInRootElement($dom, $instance->id, $serialized),
            'serialized' => $serialized,
            'dirtyInputs' => [],
        ]);
    }

    public function injectComponentDataAsHtmlAttributesInRootElement($dom, $id, $serialized)
    {
        return preg_replace(
            '/(<[a-zA-Z0-9\-]*)/',
            sprintf('$1 key="%s" %s:id="%s" %s:serialized="%s"', $id, $this->prefix, $id, $this->prefix, $serialized),
            $dom,
            $limit = 1
        );
    }

    public function test($name)
    {
        return new TestableLivewire($name, $this->prefix);
    }
}
