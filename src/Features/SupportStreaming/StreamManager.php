<?php

namespace Livewire\Features\SupportStreaming;

class StreamManager
{
    protected $component;

    protected $hook;

    protected $name;

    protected $el;

    protected $ref;

    protected $content;

    protected $replace;

    public function __construct($component, $hook)
    {
        $this->component = $component;
        $this->hook = $hook;
    }

    public function content($content, $replace = false)
    {
        $this->content = $content;
        $this->replace = $replace;

        return $this;
    }

    public function to($name = null, $el = null, $ref = null)
    {
        $this->name = $name;
        $this->el = $el;
        $this->ref = $ref;

        $this->hook->stream($this->content, $this->replace, $this->name, $this->el, $this->ref);

        return $this;
    }
}