<?php

namespace Livewire;

use Exception;
use Illuminate\Support\Facades\File;
use Livewire\Connection\ComponentHydrator;
use Livewire\Testing\TestableLivewire;

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

    public function getComponentClass($alias)
    {
        throw_unless(isset($this->componentAliases[$alias]), new Exception(
            "Component not registered: [{$alias}]"
        ));

        return $this->componentAliases[$alias];
    }

    public function activate($component)
    {
        $componentClass = $this->getComponentClass($component);

        throw_unless(class_exists($this->componentAliases[$component]), new Exception(
            "Component [{$component}] class not found: [{$componentClass}]"
        ));

        return new $componentClass;
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
        $id = str_random(20);

        $instance->mount(...$options);
        $dom = $instance->output();
        $properties = ComponentHydrator::dehydrate($instance);
        $events = $instance->getEventsBeingListenedFor();

        return new LivewireOutput([
            'id' => $id,
            'dom' => $this->injectComponentDataAsHtmlAttributesInRootElement($dom, $id, get_class($instance), $events, $properties),
            'data' => $properties,
            'dirtyInputs' => [],
            'listeningFor' => $events,
            'eventQueue' => [],
        ]);
    }

    public function injectComponentDataAsHtmlAttributesInRootElement($dom, $id, $class, $events, $properties)
    {
        $attributesFormattedForHtmlElement = collect([
            "{$this->prefix}:id" => $id,
            "{$this->prefix}:class" => $class,
            "{$this->prefix}:initial-data" => $this->escapeStringForHtml($properties),
            "{$this->prefix}:listening-for" => $this->escapeStringForHtml($events),
        ])->map(function ($value, $key) {
            return sprintf('%s="%s"', $key, $value);
        })->implode(' ');

        return preg_replace(
            '/(<[a-zA-Z0-9\-]*)/',
            sprintf('$1 %s', $attributesFormattedForHtmlElement),
            $dom,
            $limit = 1
        );
    }

    public function escapeStringForHtml($subject)
    {
        return
        addcslashes(
            htmlspecialchars(
                json_encode($subject)
            ),
            '\\'
        );
    }

    public function test($name)
    {
        return new TestableLivewire($name, $this->prefix);
    }
}
