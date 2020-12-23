<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\LivewireManager;
use Livewire\Exceptions\CorruptComponentPayloadException;

class RequestManipulatingMiddlewareAreDisabledForLivewireRequestsTest extends TestCase
{
    /** @test */
    public function livewire_request_data_doesnt_get_manipulated()
    {
        // This test is better done as a Laravel dusk test now.
        $this->markTestSkipped();

        // LivewireManager::$isLivewireRequestTestingOverride = true;

        // $this->refreshApplication();

        // $component = app(LivewireManager::class)->test(ComponentWithStringPropertiesStub::class);

        // $this->withHeader('X-Livewire', 'true')->post("/livewire/message/{$component->componentName}", [
        //     'updateQueue' => [],
        //     'name' => $component->componentName,
        //     'children' => $component->payload['children'],
        //     'data' => $component->payload['data'],
        //     'meta' => $component->payload['meta'],
        //     'id' => $component->payload['id'],
        //     'checksum' => $component->payload['checksum'],
        //     'locale' => $component->payload['locale'],
        //     'fromPrefetch' => [],
        // ])->assertJson(['data' => [
        //     'emptyString' => '',
        //     'oneSpace' => ' ',
        // ]]);

        // LivewireManager::$isLivewireRequestTestingOverride = null;

        // $this->refreshApplication();
    }

    /** @test */
    public function non_livewire_requests_do_get_manipulated()
    {
        // This test is better done as a Laravel dusk test now.
        $this->markTestSkipped();

        // $this->expectException(CorruptComponentPayloadException::class);

        // $component = app(LivewireManager::class)->test(ComponentWithStringPropertiesStub::class);

        // $this->withMiddleware()->post("/livewire/message/{$component->componentName}", [
        //     'updateQueue' => [],
        //     'name' => $component->componentName,
        //     'children' => $component->payload['children'],
        //     'data' => $component->payload['data'],
        //     'meta' => $component->payload['meta'],
        //     'id' => $component->payload['id'],
        //     'checksum' => $component->payload['checksum'],
        //     'locale' => $component->payload['locale'],
        //     'fromPrefetch' => [],
        // ])->assertJson(['data' => [
        //     'emptyString' => null,
        //     'oneSpace' => null,
        // ]]);
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
