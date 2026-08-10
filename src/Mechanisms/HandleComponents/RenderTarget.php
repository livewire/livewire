<?php

namespace Livewire\Mechanisms\HandleComponents;

class RenderTarget
{
    protected function __construct(
        public $type,
        public $name = null,
    ) {}

    public static function root()
    {
        return new static('root');
    }

    public static function island($name)
    {
        return new static('island', $name);
    }

    public function key()
    {
        return $this->isRoot() ? 'root' : 'island:'.$this->name;
    }

    public function isRoot()
    {
        return $this->type === 'root';
    }

    public function isIsland()
    {
        return $this->type === 'island';
    }
}
