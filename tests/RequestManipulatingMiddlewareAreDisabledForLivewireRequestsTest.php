<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Livewire\Exceptions\CorruptComponentPayloadException;

class RequestManipulatingMiddlewareAreDisabledForLivewireRequestsTest extends TestCase
{
    /** @test */
    public function livewire_request_data_doesnt_get_manipulated()
    {
        LivewireManager::$isLivewireRequestTestingOverride = true;

        $this->refreshApplication();

        $component = app(LivewireManager::class)->test(ComponentWithStringPropertiesStub::class);

        $this->withHeader('X-Livewire', 'true')->post("/livewire/message/{$component->componentName}", [
            'actionQueue' => [],
            'name' => $component->componentName,
            'children' => $component->payload['children'],
            'data' => $component->payload['data'],
            'id' => $component->payload['id'],
            'checksum' => $component->payload['checksum'],
            'fromPrefetch' => [],
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

        $this->withMiddleware()->post("/livewire/message/{$component->componentName}", [
            'actionQueue' => [],
            'name' => $component->componentName,
            'children' => $component->payload['children'],
            'data' => $component->payload['data'],
            'id' => $component->payload['id'],
            'checksum' => $component->payload['checksum'],
            'fromPrefetch' => [],
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
