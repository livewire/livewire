<?php

namespace Livewire\Mechanisms\HandleComponents;

class RenderCall
{
    public function __construct(
        public $index,
        public $method,
        public $resolvedMethod,
        public RenderTarget $target,
        public $mode = 'morph',
        public $renderless = false,
        public $mountIsland = false,
    ) {}
}
