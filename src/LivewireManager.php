<?php

namespace Livewire;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
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

    public function mount($name, ...$options)
    {
        $instance = $this->activate($name);
        $id = str_random(20);

        $instance->mount(...$options);
        $dom = $instance->output();
        $properties = ComponentHydrator::dehydrate($instance);
        $events = $instance->getEventsBeingListenedFor();
        $children = $instance->getRenderedChildren();
        $checksum = Hash::make($name);

        return new LivewireOutput([
            'id' => $id,
            'dom' => $this->injectComponentDataAsHtmlAttributesInRootElement($dom, [
                'id' => $id,
                'name' => $name,
                'children' => $children,
                'initial-data' => $properties,
                'checksum' => $checksum,
                'listening-for' => $events,
                'middleware' => encrypt($this->currentMiddlewareStack(), $serialize = true),
            ]),
            'checksum' => $checksum,
            'data' => $properties,
            'children' => $children,
            'dirtyInputs' => [],
            'listeningFor' => $events,
            'eventQueue' => [],
        ]);
    }

    public function currentMiddlewareStack()
    {
        if (app()->runningUnitTests()) {
            // There is no "request->route()" to access in unit tests.
            return [];
        }

        return request()->route()->gatherMiddleware();
    }

    public function dummyMount($id)
    {
        return "<div wire:id=\"{$id}\"></div>";
    }

    public function injectComponentDataAsHtmlAttributesInRootElement($dom, $data)
    {
        $attributesFormattedForHtmlElement = collect($data)
            ->mapWithKeys(function ($value, $key) {
                return ["{$this->prefix}:{$key}" => $this->escapeStringForHtml($value)];
            })->map(function ($value, $key) {
                return sprintf('%s="%s"', $key, $value);
            })->implode(' ');

        preg_match('/<[a-zA-Z0-9\-]*([\s>])/', $dom, $matches, PREG_OFFSET_CAPTURE);
        $positionOfFirstSpaceCharacterAfterTagName = $matches[1][1];

        return substr_replace(
            $dom,
            $attributesFormattedForHtmlElement . ' ',
            $positionOfFirstSpaceCharacterAfterTagName + 1,
            0
        );
    }

    public function escapeStringForHtml($subject)
    {
        if (is_string($subject) || is_numeric($subject)) {
            return $subject;
        }

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
