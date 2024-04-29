<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class QueryParamsUnitTest extends \Tests\TestCase
{
    public function test_it_sets_name_from_query_params()
    {
        $name = 'Livewire';

        Livewire::withQueryParams(['name' => $name])
            ->test(QueryParamsComponent::class)
            ->assertSet('name', $name);
    }

    public function test_it_does_not_set_name_when_no_query_params_are_provided()
    {
        Livewire::test(QueryParamsComponent::class)
            ->assertSet('name', null);
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
