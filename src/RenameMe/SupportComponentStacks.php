<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;
use Livewire\Response;
use Livewire\Component;

class SupportComponentStacks
{
    protected $stacks = [];

    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('view:render', function ($view) {
            $component = data_get($view->getData(), '_instance');

            if ($component instanceof Component) {
                foreach ($component->getStacks() as $section => $content) {
                    $this->stacks[$section][$component->id] = $content;
                }
            }
        });

        Livewire::listen(
            'component.dehydrate.subsequent',
            function (Component $component, Response $response) {
                if ($this->stacks) {
                    // Sort by components in higher level first.
                    $response->effects['stack'] = array_map('array_reverse', $this->stacks);
                }
            }
        );
    }
}
