<?php

namespace Livewire\Features\SupportSlots;

use Illuminate\Contracts\Support\Htmlable;

class PlaceholderSlot implements Htmlable
{
    public function __construct(
        public string $name,
        public string $componentId,
        public ?string $parentComponentId = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getComponentId(): string
    {
        return $this->componentId;
    }

    public function getParentId(): ?string
    {
        return $this->parentComponentId;
    }

    public function toHtml(): string
    {
        $parentPart = $this->parentComponentId ? ['parent' => $this->parentComponentId] : [];

        return $this->wrapWithFragmentMarkers('', [
            'name' => $this->name,
            'type' => 'slot',
            'id' => $this->componentId,
            // This is here for JS to match opening and close markers...
            'token' => crc32($this->name . $this->componentId . $this->parentComponentId ?? ''),
            'mode' => 'skip',
            ...$parentPart,
        ]);
    }

    protected function wrapWithFragmentMarkers($output, $metadata)
    {
        $startFragment = "<!--[if FRAGMENT:{$this->encodeFragmentMetadata($metadata)}]><![endif]-->";

        $endFragment = "<!--[if ENDFRAGMENT:{$this->encodeFragmentMetadata($metadata)}]><![endif]-->";

        return $startFragment . $output . $endFragment;
    }

    protected function encodeFragmentMetadata($metadata)
    {
        $output = '';

        foreach ($metadata as $key => $value) {
            $output .= "{$key}={$value}|";
        }

        return rtrim($output, '|');
    }
}
