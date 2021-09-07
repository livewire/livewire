<?php

namespace Tests\Unit\Components;

use Illuminate\Routing\UrlGenerator;
use Livewire\Component;

class ComponentWithUnionTypes extends Component
{
    public $foo;
    public $bar;

    public function mount(UrlGenerator $generator, string|int $id = 123)
    {
        $this->foo = $generator->to("/some-url", $id);
        $this->bar = $id;
    }

    public function injection(UrlGenerator $generator, $bar)
    {
        $this->foo = $generator->to("/");
        $this->bar = $bar;
    }

    public function render()
    {
        return view("null-view");
    }
}
