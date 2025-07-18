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

    public function island($name, $view, $data = [], $mode = 'replace')
    {
        SupportStreaming::ensureStreamResponseStarted();

        $id = $this->component->id();

        $island = $this->component->island($name, $view, $data);

        // @todo: This is a hack to pop the island off the component's islands array...
        $this->component->popLastIsland();

        SupportStreaming::streamContent([
            'type' => 'island',
            'id' => $id,
            'name' => $name,
            'content' => $island->render(),
            'mode' => $mode,
        ]);
    }
}
