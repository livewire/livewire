<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class RequestManipulatingMiddlewareAreDisabledForLivewireRequestsTest extends TestCase
{
    /** @test */
    public function public_id_property_is_set()
    {
        LivewireManager::$isLivewireRequestTestingOverride = true;

        $this->refreshApplication();

        $component = app(LivewireManager::class)->test(ComponentWithStringPropertiesStub::class);

        $this->withHeader('X-Livewire', 'true')->post("/livewire/message/{$component->name}", [
            'actionQueue' => [],
            'name' => $component->name,
            'children' => $component->children,
            'data' => $component->data,
            'id' => $component->id,
            'checksum' => $component->checksum,
            'fromPrefetch' => [],
            'gc' => $component->gc,
        ])->assertJson(['data' => [
            'emptyString' => '',
            'oneSpace' => ' ',
        ]]);
    }
}

class ComponentWithStringPropertiesStub extends Component
{
    public $emptyString = '';
    public $oneSpace = ' ';

    public function render()
    {
        return app('view')->make('null-view');
    }
}
