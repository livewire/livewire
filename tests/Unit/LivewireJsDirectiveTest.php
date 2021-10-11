<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Testing\TestView;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\ExpectationFailedException;

class LivewireJsDirectiveTest extends TestCase
{
    /** @test */
    public function single_quotes()
    {
        Livewire::test(ComponentForTestingJsDirective::class)
            ->assertDontSee('@this')
            ->assertSee('window.livewire.find(');
    }
}

class ComponentForTestingJsDirective extends Component
{
    public function render()
    {
        return view('this-directive');
    }
}
