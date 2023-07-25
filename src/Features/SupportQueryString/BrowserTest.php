<?php

namespace Livewire\Features\SupportQueryString;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Laravel\Dusk\Browser;
use LegacyTests\Browser\QueryString\ComponentWithExcepts;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\WithPagination;
use Sushi\Sushi;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function it_will_remove_empty_input()
    {
        Livewire::withQueryParams(['page' => ''])->visit(TestComponentWithOutHistory::class)
            ->assertQueryStringMissing('page');
        Livewire::withQueryParams(['page' => ''])->visit(TestComponentWithHistory::class)
            ->assertQueryStringMissing('page');
    }
}

class TestComponentWithOutHistory extends Component
{
    #[Url(keep: false)]
    public int $page;

    public function render()
    {
        return Blade::render(
            <<< 'HTML'
                    <div>
                        
                    </div>
                    HTML
        );
    }
}

class TestComponentWithHistory extends Component
{
    #[Url(history: true, keep: false)]
    public int $page;

    public function render()
    {
        return Blade::render(
            <<< 'HTML'
                    <div>
                        
                    </div>
                    HTML
        );
    }
}