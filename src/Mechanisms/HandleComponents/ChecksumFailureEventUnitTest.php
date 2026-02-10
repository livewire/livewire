<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\Events\ChecksumFailure;
use Livewire\Features\SupportReleaseTokens\ReleaseToken;
use Livewire\Livewire;
use Tests\TestCase;

class ChecksumFailureEventUnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('livewire-checksum-failures:127.0.0.1');

        Livewire::component('test-component', ChecksumFailureEventTestComponent::class);
    }

    public function test_checksum_failure_fires_event()
    {
        Event::fake([ChecksumFailure::class]);

        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumFailureEventTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        try {
            Checksum::verify($snapshot);
        } catch (CorruptComponentPayloadException $e) {
            // Expected
        }

        Event::assertDispatched(ChecksumFailure::class, function ($event) {
            return $event->ipAddress === '127.0.0.1'
                && $event->componentName === 'test-component';
        });
    }

    public function test_checksum_failure_event_includes_user_agent()
    {
        Event::fake([ChecksumFailure::class]);

        request()->headers->set('User-Agent', 'TestBrowser/1.0');

        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumFailureEventTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        try {
            Checksum::verify($snapshot);
        } catch (CorruptComponentPayloadException $e) {
            // Expected
        }

        Event::assertDispatched(ChecksumFailure::class, function ($event) {
            return $event->userAgent === 'TestBrowser/1.0';
        });
    }

    public function test_checksum_failure_event_fires_on_each_failure()
    {
        Event::fake([ChecksumFailure::class]);

        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumFailureEventTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        for ($i = 0; $i < 3; $i++) {
            request()->attributes->remove('livewire_rate_limit_checked');

            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        Event::assertDispatchedTimes(ChecksumFailure::class, 3);
    }

    public function test_valid_checksum_does_not_fire_event()
    {
        Event::fake([ChecksumFailure::class]);

        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumFailureEventTestComponent::class)],
            'data' => ['foo' => 'bar'],
        ];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        Checksum::verify($snapshot);

        Event::assertNotDispatched(ChecksumFailure::class);
    }
}

class ChecksumFailureEventTestComponent extends Component
{
    public function render()
    {
        return '<div></div>';
    }
}
