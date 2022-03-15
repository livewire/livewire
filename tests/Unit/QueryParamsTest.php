<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class QueryParamsTest extends TestCase
{
    /** @test */
    public function it_sets_name_from_query_params()
    {
        $name = 'Livewire';

        Livewire::withQueryParams(['name' => $name])
            ->test(QueryParamsComponent::class)
            ->assertSet('name', $name);
    }

    /** @test */
    public function it_does_not_set_name_when_no_query_params_are_provided()
    {
        Livewire::test(QueryParamsComponent::class)
            ->assertSet('name', null);
    }

    /** @test */
    public function it_sets_only_some_filters_from_query_params()
    {
        $search = 'Livewire';

        Livewire::withQueryParams(['filters' => [ 'search' => $search ]])
            ->test(QueryParamsWithArrayFiltersPropertyComponent::class)
            ->assertSet('filters', [ 'search' => $search, 'category' => '' ]);
    }
}

class QueryParamsComponent extends Component
{
    public $name;

    public function mount()
    {
        $this->name = request('name');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class QueryParamsWithArrayFiltersPropertyComponent extends Component
{
    public $filters = [
        'search' => '',
        'category' => ''
    ];

    protected $queryString = ['filters'];

    public function render()
    {
        return app('view')->make('null-view');
    }
}
