<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\Component;

class StreamManager
{
    public function __construct(public Component $component) {}

    public function update($dataKey, $value = null, $replace = true)
    {
        SupportStreaming::ensureStreamResponseStarted();

        $value = $value ?? $this->component->getPropertyValue($dataKey);

        $id = $this->component->id();

        SupportStreaming::streamContent([
            'type' => 'update',
            'id' => $id,
            'key' => $dataKey,
            'value' => $value,
            'replace' => $replace,
        ]);
    }

    public function html($name, $content, $replace = false)
    {
        SupportStreaming::ensureStreamResponseStarted();

        $id = $this->component->id();

        SupportStreaming::streamContent([
            'type' => 'html',
            'id' => $id,
            'name' => $name,
            'content' => $content,
            'replace' => $replace,
        ]);
    }
}
