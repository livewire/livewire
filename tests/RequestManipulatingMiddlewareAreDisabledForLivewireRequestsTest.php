<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Livewire\Exceptions\CorruptComponentPayloadException;

class RequestManipulatingMiddlewareAreDisabledForLivewireRequestsTest extends TestCase
{
    /** @test */
    public function livewire_request_data_doesnt_dont_get_manipulated()
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

        LivewireManager::$isLivewireRequestTestingOverride = null;

        $this->refreshApplication();
    }

    /** @test */
    public function non_livewire_requests_do_get_manipulated()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        $component = app(LivewireManager::class)->test(ComponentWithStringPropertiesStub::class);

        $this->post("/livewire/message/{$component->name}", [
            'actionQueue' => [],
            'name' => $component->name,
            'children' => $component->children,
            'data' => $component->data,
            'id' => $component->id,
            'checksum' => $component->checksum,
            'fromPrefetch' => [],
            'gc' => $component->gc,
        ])->assertJson(['data' => [
            'emptyString' => null,
            'oneSpace' => null,
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
