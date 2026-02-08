<?php

namespace Livewire\Mechanisms\HandleRequests;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Exceptions\PayloadTooLargeException;
use Livewire\Exceptions\TooManyComponentsException;
use Livewire\Exceptions\TooManyCallsException;
use Tests\TestCase;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class PayloadGuardsUnitTest extends TestCase
{
    public function test_rejects_payload_exceeding_max_size()
    {
        config()->set('livewire.payload.max_size', 100); // 100 bytes

        $this->expectException(PayloadTooLargeException::class);
        $this->expectExceptionMessage('payload.max_size');

        // Simulate a request with a large Content-Length header
        $this->withoutExceptionHandling()
            ->withHeaders(['Content-Length' => 1000, 'X-Livewire' => 'true'])
            ->post(EndpointResolver::updatePath(), [
                'components' => [
                    [
                        'snapshot' => json_encode([
                            'data' => [],
                            'memo' => ['id' => 'test', 'name' => 'test'],
                            'checksum' => 'test',
                        ]),
                        'updates' => [],
                        'calls' => [],
                    ],
                ],
            ]);
    }

    public function test_allows_payload_within_max_size()
    {
        config()->set('livewire.payload.max_size', 1024 * 1024); // 1MB

        $component = Livewire::test(PayloadGuardComponent::class);

        // Should not throw
        $component->call('increment');

        $this->assertEquals(1, $component->get('count'));
    }

    public function test_payload_size_limit_can_be_disabled()
    {
        config()->set('livewire.payload.max_size', null);

        $component = Livewire::test(PayloadGuardComponent::class);

        // Should not throw even with default null limit
        $component->call('increment');

        $this->assertEquals(1, $component->get('count'));
    }

    public function test_rejects_too_many_components()
    {
        config()->set('livewire.payload.max_components', 2);

        $this->expectException(TooManyComponentsException::class);
        $this->expectExceptionMessage('payload.max_components');

        // Create a request with 3 components (exceeds limit of 2)
        $this->withoutExceptionHandling()
            ->withHeaders(['X-Livewire' => 'true'])
            ->post(EndpointResolver::updatePath(), [
                'components' => [
                    ['snapshot' => '{}', 'updates' => [], 'calls' => []],
                    ['snapshot' => '{}', 'updates' => [], 'calls' => []],
                    ['snapshot' => '{}', 'updates' => [], 'calls' => []],
                ],
            ]);
    }

    public function test_allows_components_within_limit()
    {
        config()->set('livewire.payload.max_components', 10);

        $component = Livewire::test(PayloadGuardComponent::class);

        // Single component should be fine
        $component->call('increment');

        $this->assertEquals(1, $component->get('count'));
    }

    public function test_max_components_limit_can_be_disabled()
    {
        config()->set('livewire.payload.max_components', null);

        $component = Livewire::test(PayloadGuardComponent::class);

        // Should work with null limit
        $component->call('increment');

        $this->assertEquals(1, $component->get('count'));
    }

    public function test_rejects_too_many_calls()
    {
        config()->set('livewire.payload.max_calls', 3);

        // First mount the component to get a valid snapshot
        $component = Livewire::test(PayloadGuardComponent::class);
        $snapshot = $component->snapshot;

        $this->expectException(TooManyCallsException::class);
        $this->expectExceptionMessage('payload.max_calls');

        // Send a request with 4 calls (exceeds limit of 3)
        $this->withoutExceptionHandling()
            ->withHeaders(['X-Livewire' => 'true'])
            ->post(EndpointResolver::updatePath(), [
                'components' => [
                    [
                        'snapshot' => json_encode($snapshot),
                        'updates' => [],
                        'calls' => [
                            ['method' => 'increment', 'params' => [], 'metadata' => []],
                            ['method' => 'increment', 'params' => [], 'metadata' => []],
                            ['method' => 'increment', 'params' => [], 'metadata' => []],
                            ['method' => 'increment', 'params' => [], 'metadata' => []],
                        ],
                    ],
                ],
            ]);
    }

    public function test_allows_calls_within_limit()
    {
        config()->set('livewire.payload.max_calls', 5);

        $component = Livewire::test(PayloadGuardComponent::class);

        // Make 3 calls (within limit of 5)
        $component->call('increment')
            ->call('increment')
            ->call('increment');

        $this->assertEquals(3, $component->get('count'));
    }

    public function test_max_calls_limit_can_be_disabled()
    {
        config()->set('livewire.payload.max_calls', null);

        $component = Livewire::test(PayloadGuardComponent::class);

        // Should work with null limit
        $component->call('increment')
            ->call('increment')
            ->call('increment')
            ->call('increment')
            ->call('increment');

        $this->assertEquals(5, $component->get('count'));
    }

    public function test_allows_exactly_max_calls()
    {
        config()->set('livewire.payload.max_calls', 3);

        $component = Livewire::test(PayloadGuardComponent::class);

        // Exactly 3 calls should be allowed
        $component->call('increment')
            ->call('increment')
            ->call('increment');

        $this->assertEquals(3, $component->get('count'));
    }

    public function test_exception_messages_include_config_keys()
    {
        $payloadException = new PayloadTooLargeException(2048, 1024);
        $this->assertStringContainsString('payload.max_size', $payloadException->getMessage());
        $this->assertStringContainsString('2KB', $payloadException->getMessage());
        $this->assertStringContainsString('1KB', $payloadException->getMessage());

        $componentsException = new TooManyComponentsException(30, 20);
        $this->assertStringContainsString('payload.max_components', $componentsException->getMessage());
        $this->assertStringContainsString('30', $componentsException->getMessage());
        $this->assertStringContainsString('20', $componentsException->getMessage());

        $callsException = new TooManyCallsException(60, 50);
        $this->assertStringContainsString('payload.max_calls', $callsException->getMessage());
        $this->assertStringContainsString('60', $callsException->getMessage());
        $this->assertStringContainsString('50', $callsException->getMessage());
    }
}

class PayloadGuardComponent extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}
