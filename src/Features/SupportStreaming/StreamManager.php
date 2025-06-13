<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\Component;

class StreamManager
{
    public function __construct(public Component $component) {}

    public function update($dataKey, $value = null, $mode = 'replace')
    {
        SupportStreaming::ensureStreamResponseStarted();

        $value = $value ?? $this->component->getPropertyValue($dataKey);

        $id = $this->component->id();

        SupportStreaming::streamContent([
            'type' => 'update',
            'id' => $id,
            'key' => $dataKey,
            'value' => $value,
            'mode' => $mode,
        ]);
    }

    public function html($name, $content, $mode = 'replace')
    {
        SupportStreaming::ensureStreamResponseStarted();

        $id = $this->component->id();

        SupportStreaming::streamContent([
            'type' => 'html',
            'id' => $id,
            'name' => $name,
            'content' => $content,
            'mode' => $mode,
        ]);
    }

    public function partial($name, $view, $data = [], $mode = 'replace')
    {
        SupportStreaming::ensureStreamResponseStarted();

        $id = $this->component->id();

        $partial = $this->component->partial($name, $view, $data);

        // @todo: This is a hack to pop the partial off the component's partials array...
        $this->component->popLastPartial();

        SupportStreaming::streamContent([
            'type' => 'partial',
            'id' => $id,
            'name' => $name,
            'content' => $partial->render(),
            'mode' => $mode,
        ]);
    }
}
