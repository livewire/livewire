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
        $component = app(LivewireManager::class)->test(UpdatesQueryStringWithExceptsStub::class);

        $component->set('foo', 'bar');
        $this->assertEquals(['foo' => ['except' => 'bar']], $component->payload['updatesQueryString']);
        $this->assertEquals('bar', $component->payload['data']['foo']);
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

class UpdatesQueryStringWithExceptsStub extends Component
{
    public $foo = 'bar';

    protected $updatesQueryString = [
        'foo' => ['except' => 'bar'],
    ];

    public function render()
    {
        return app('view')->make('null-view');
    }
}
