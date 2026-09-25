<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Livewire\Features\SupportReleaseTokens\ReleaseToken;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class ChecksumRateLimitUnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Enable rate limiting for these tests (disabled by default in tests)
        Checksum::enableRateLimitingForTesting();

        // Clear any existing rate limits before each test
        RateLimiter::clear('livewire-checksum-failures:127.0.0.1');
        RateLimiter::clear('livewire-checksum-failures:key:client-a');
        RateLimiter::clear('livewire-checksum-failures:key:client-b');
        RateLimiter::clear('livewire-checksum-failures:key:127.0.0.1');

        // Register a test component for use in snapshots
        Livewire::component('test-component', ChecksumRateLimitTestComponent::class);
    }

    public function tearDown(): void
    {
        Checksum::disableRateLimitingForTesting();

        Livewire::setChecksumRateLimitKey(null);

        request()->headers->remove('X-Client');
        request()->attributes->remove('livewire_rate_limit_key');

        parent::tearDown();
    }

    public function test_checksum_failure_is_recorded()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        try {
            Checksum::verify($snapshot);
        } catch (CorruptComponentPayloadException $e) {
            // Expected
        }

        // Verify a hit was recorded
        $this->assertEquals(1, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
    }

    public function test_multiple_failures_are_tracked()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        for ($i = 0; $i < 5; $i++) {
            // Clear the flag to simulate a new request
            request()->attributes->remove('livewire_rate_limit_checked');
            request()->attributes->remove('livewire_rate_limit_key');

            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        $this->assertEquals(5, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
    }

    public function test_blocks_after_max_failures()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        // Hit the rate limit (10 failures across 10 "requests")
        for ($i = 0; $i < 10; $i++) {
            // Clear the flag to simulate a new request
            request()->attributes->remove('livewire_rate_limit_checked');
            request()->attributes->remove('livewire_rate_limit_key');

            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        // Next attempt should throw TooManyRequestsHttpException
        request()->attributes->remove('livewire_rate_limit_checked');
        request()->attributes->remove('livewire_rate_limit_key');

        $this->expectException(TooManyRequestsHttpException::class);
        $this->expectExceptionMessage('Too many invalid Livewire requests');

        Checksum::verify($snapshot);
    }

    public function test_valid_checksum_does_not_record_failure()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
        ];

        // Generate valid checksum
        $snapshot['checksum'] = Checksum::generate($snapshot);

        // This should not throw
        Checksum::verify($snapshot);

        // No failures should be recorded
        $this->assertEquals(0, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
    }

    public function test_rate_limit_blocks_even_valid_requests_when_limit_exceeded()
    {
        // First, exceed the rate limit with invalid requests
        $invalidSnapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        for ($i = 0; $i < 10; $i++) {
            // Clear the flag to simulate a new request
            request()->attributes->remove('livewire_rate_limit_checked');
            request()->attributes->remove('livewire_rate_limit_key');

            try {
                Checksum::verify($invalidSnapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        // Now try with a valid checksum - should still be blocked
        request()->attributes->remove('livewire_rate_limit_checked');
        request()->attributes->remove('livewire_rate_limit_key');

        $validSnapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
        ];
        $validSnapshot['checksum'] = Checksum::generate($validSnapshot);

        $this->expectException(TooManyRequestsHttpException::class);

        Checksum::verify($validSnapshot);
    }

    public function test_rate_limit_is_only_checked_once_per_request()
    {
        RateLimiter::spy();

        $component = Livewire::test(ChecksumRateLimitTestComponent::class);
        $snapshot = $component->snapshot;

        // Verify the same snapshot multiple times within the same "request"
        // (simulating multiple components being verified)
        Checksum::verify($snapshot);
        Checksum::verify($snapshot);
        Checksum::verify($snapshot);

        // tooManyAttempts should only be called once, not three times
        RateLimiter::shouldHaveReceived('tooManyAttempts')->once();
    }

    public function test_rate_limit_is_checked_again_on_new_request()
    {
        RateLimiter::spy();

        $component = Livewire::test(ChecksumRateLimitTestComponent::class);
        $snapshot = $component->snapshot;

        // First "request" - verify multiple components
        Checksum::verify($snapshot);
        Checksum::verify($snapshot);

        // Simulate a new request by clearing the flag
        request()->attributes->remove('livewire_rate_limit_checked');
        request()->attributes->remove('livewire_rate_limit_key');

        // Second "request" - verify again
        Checksum::verify($snapshot);

        // tooManyAttempts should be called twice (once per request)
        RateLimiter::shouldHaveReceived('tooManyAttempts')->twice();
    }
    public function test_failures_are_remembered_for_ten_minutes_by_default()
    {
        $this->failChecksum();

        $this->assertEquals(1, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
        $this->assertEqualsWithDelta(600, RateLimiter::availableIn('livewire-checksum-failures:127.0.0.1'), 1);
    }

    public function test_rate_limit_key_can_be_customized()
    {
        Livewire::setChecksumRateLimitKey(fn (Request $request) => $request->header('X-Client'));

        request()->headers->set('X-Client', 'client-a');

        $this->failChecksum(10);

        $this->assertEquals(10, RateLimiter::attempts('livewire-checksum-failures:key:client-a'));
        $this->assertEquals(0, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));

        // Another client sharing the same IP address is not blocked...
        request()->headers->set('X-Client', 'client-b');

        $this->verifyValidChecksum();

        // While the client that produced the failures is...
        request()->headers->set('X-Client', 'client-a');

        $this->expectException(TooManyRequestsHttpException::class);

        $this->verifyValidChecksum();
    }

    public function test_custom_rate_limit_key_falls_back_to_ip_address_when_null()
    {
        Livewire::setChecksumRateLimitKey(fn (Request $request) => $request->header('X-Client'));

        $this->failChecksum();

        $this->assertEquals(1, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
    }

    public function test_custom_rate_limit_key_falls_back_to_ip_address_when_empty()
    {
        Livewire::setChecksumRateLimitKey(fn () => '');

        $this->failChecksum();

        Livewire::setChecksumRateLimitKey(fn () => false);

        $this->failChecksum();

        $this->assertEquals(2, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
        $this->assertEquals(0, RateLimiter::attempts('livewire-checksum-failures:key:'));
    }

    public function test_custom_rate_limit_key_cannot_collide_with_an_ip_address()
    {
        Livewire::setChecksumRateLimitKey(fn (Request $request) => $request->header('X-Client'));

        request()->headers->set('X-Client', '127.0.0.1');

        $this->failChecksum(10);

        $this->assertEquals(0, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));

        // A client identified by that IP address is not blocked...
        request()->headers->remove('X-Client');

        $this->verifyValidChecksum();
    }

    public function test_rate_limit_key_is_resolved_once_per_request()
    {
        $calls = 0;

        Livewire::setChecksumRateLimitKey(function () use (&$calls) {
            return 'client-' . ++$calls;
        });

        RateLimiter::spy();

        $snapshot = $this->invalidSnapshot();

        // One request, two failing components...
        foreach ([1, 2] as $i) {
            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        $this->assertEquals(1, $calls);

        RateLimiter::shouldHaveReceived('tooManyAttempts')->with('livewire-checksum-failures:key:client-1', 10)->once();
        RateLimiter::shouldHaveReceived('hit')->with('livewire-checksum-failures:key:client-1', 600)->twice();

        // A new request resolves its own key...
        $this->failChecksum();

        $this->assertEquals(2, $calls);

        RateLimiter::shouldHaveReceived('hit')->with('livewire-checksum-failures:key:client-2', 600)->once();
    }

    public function test_max_failures_can_be_configured()
    {
        config()->set('livewire.checksum_rate_limit.max_failures', 3);

        $this->failChecksum(3);

        $this->expectException(TooManyRequestsHttpException::class);

        $this->verifyValidChecksum();
    }

    public function test_decay_seconds_can_be_configured()
    {
        config()->set('livewire.checksum_rate_limit.decay_seconds', 60);

        $this->failChecksum();

        $this->assertEqualsWithDelta(60, RateLimiter::availableIn('livewire-checksum-failures:127.0.0.1'), 1);
    }

    public function test_rate_limiting_can_be_disabled()
    {
        config()->set('livewire.checksum_rate_limit.max_failures', null);

        $this->failChecksum(20);

        $this->assertEquals(0, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));

        $this->verifyValidChecksum();
    }

    public function test_invalid_checksum_is_still_rejected_when_rate_limiting_is_disabled()
    {
        config()->set('livewire.checksum_rate_limit.max_failures', null);

        $this->expectException(CorruptComponentPayloadException::class);

        Checksum::verify($this->invalidSnapshot());
    }

    public function test_rate_limiting_is_disabled_when_max_failures_is_zero_or_false()
    {
        foreach ([0, false] as $value) {
            config()->set('livewire.checksum_rate_limit.max_failures', $value);

            $this->failChecksum(3);

            $this->assertEquals(0, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));

            $this->verifyValidChecksum();
        }
    }

    protected function invalidSnapshot()
    {
        return [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];
    }

    protected function failChecksum($times = 1)
    {
        $snapshot = $this->invalidSnapshot();

        for ($i = 0; $i < $times; $i++) {
            // Clear the flag to simulate a new request
            request()->attributes->remove('livewire_rate_limit_checked');
            request()->attributes->remove('livewire_rate_limit_key');

            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }
    }

    protected function verifyValidChecksum()
    {
        request()->attributes->remove('livewire_rate_limit_checked');
        request()->attributes->remove('livewire_rate_limit_key');

        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
        ];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        Checksum::verify($snapshot);
    }
}

class ChecksumRateLimitTestComponent extends Component
{
    public function render()
    {
        return '<div></div>';
    }
}
