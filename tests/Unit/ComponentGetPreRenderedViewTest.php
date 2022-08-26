<?php

namespace Tests\Unit;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ComponentGetPreRenderedViewTest extends TestCase
{
    /** @test */
    public function it_should_get_the_pre_rendered_view()
    {
        $component = new ComponentExample('fake-id');

        $component->renderToView();

        $this->assertNotNull($component->getPreRenderedView());
        $this->assertInstanceOf(View::class, $component->getPreRenderedView());
    }
}

class ComponentExample extends Component
{
    public function render()
    {
        return view('null-view');
    }
}
