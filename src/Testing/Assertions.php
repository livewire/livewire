<?php

namespace Livewire\Testing;

use Livewire\Component;
use PHPUnit\Framework\Assert;

class Assertions
{
    protected $content;

    protected $component;

    public function __construct($content, $component)
    {
        $this->content = $content;
        $this->component = is_subclass_of($component, Component::class) ? $component::getName(): $component;
    }

    public function seeComponent()
    {
        Assert::assertStringContainsString(
            $this->escapedComponentName(),
            $this->content,
            'Cannot find Livewire component ['.$this->component.']'
        );
    }

    public function dontSeeComponent()
    {
        Assert::assertStringNotContainsString(
            $this->escapedComponentName(),
            $this->content,
            'Found Livewire component ['.$this->component.']'
        );
    }

    protected function escapedComponentName()
    {
        return trim(htmlspecialchars(json_encode(['name' => $this->component])), '{}');
    }
}
