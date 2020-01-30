<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class UpdatesQueryStringTest extends TestCase
{
    /** @test */
    public function specified_properties_update_query_string()
    {
        $component = app(LivewireManager::class)->test(UpdatesQueryStringStub::class);

        $component->set('foo', 'baz');

        $this->assertEquals(['foo'], $component->payload['updatesQueryString']);
        $this->assertEquals('baz', $component->payload['data']['foo']);
    }

    /** @test */
    public function can_remove_items_from_query_string_if_except_is_specified()
    {
        //
    }
}

class UpdatesQueryStringStub extends Component
{
    public $foo = 'bar';

    protected $updatesQueryString = ['foo'];

    public function render()
    {
        return app('view')->make('null-view');
    }
}
