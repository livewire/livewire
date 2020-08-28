<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class UpdatesHistoryPathTest extends TestCase
{
	/** @test */
	public function no_path_data_is_present_by_default()
	{
		$component = app(LivewireManager::class)->test(DoesNotUpdateHistoryPathStub::class);
		
		$component->set('foo', 'baz');
		
		$this->assertFalse(isset($component->payload['historyPath']));
	}
	
    /** @test */
    public function changing_properties_updates_history_path()
    {
        $component = app(LivewireManager::class)->test(UpdatesHistoryPathStub::class);

        $component->set('foo', 'baz');

        $this->assertEquals('/foo/baz', $component->payload['historyPath']);
        $this->assertEquals('baz', $component->payload['data']['foo']);
    }
}

class UpdatesHistoryPathStub extends Component
{
    public $foo = 'bar';
    
    public function mapStateToUrl()
    {
	    return url("/foo/{$this->foo}");
    }
	
	public function render()
    {
        return app('view')->make('null-view');
    }
}

class DoesNotUpdateHistoryPathStub extends Component
{
	public $foo = 'bar';
	
	public function render()
	{
		return app('view')->make('null-view');
	}
}
