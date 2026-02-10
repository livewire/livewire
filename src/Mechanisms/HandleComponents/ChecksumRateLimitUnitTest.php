<?php

namespace Livewire\Mechanisms\HandleComponents;

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

        // Clear any existing rate limits before each test
        RateLimiter::clear('livewire-checksum-failures:127.0.0.1');
        RateLimiter::clear('livewire-checksum-failures:global');

        // Register a test component for use in snapshots
        Livewire::component('test-component', ChecksumRateLimitTestComponent::class);
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

        // Hit the rate limit (5 failures across 5 "requests")
        for ($i = 0; $i < 5; $i++) {
            // Clear the flag to simulate a new request
            request()->attributes->remove('livewire_rate_limit_checked');

            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        // Next attempt should throw TooManyRequestsHttpException
        request()->attributes->remove('livewire_rate_limit_checked');

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

        for ($i = 0; $i < 5; $i++) {
            // Clear the flag to simulate a new request
            request()->attributes->remove('livewire_rate_limit_checked');

            try {
                Checksum::verify($invalidSnapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        // Now try with a valid checksum - should still be blocked
        request()->attributes->remove('livewire_rate_limit_checked');

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

        // tooManyAttempts should only be called twice (global + per-IP),
        // not six times — the once-per-request flag prevents re-checking.
        RateLimiter::shouldHaveReceived('tooManyAttempts')->twice();
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

        // Second "request" - verify again
        Checksum::verify($snapshot);

        // tooManyAttempts should be called 4 times (global + per-IP, twice each for 2 requests)
        RateLimiter::shouldHaveReceived('tooManyAttempts')->times(4);
    }

    public function test_failure_is_recorded_to_global_rate_limiter()
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

        // Verify a hit was recorded against both the per-IP and global keys
        $this->assertEquals(1, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
        $this->assertEquals(1, RateLimiter::attempts('livewire-checksum-failures:global'));
    }

    public function test_global_rate_limit_blocks_when_threshold_exceeded()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        // Simulate 50 failures hitting the global limit by pre-filling
        // the global rate limiter (as if from many different IPs).
        for ($i = 0; $i < 50; $i++) {
            RateLimiter::hit('livewire-checksum-failures:global', 60);
        }

        // Clear per-IP limiter so the global limit is what triggers
        RateLimiter::clear('livewire-checksum-failures:127.0.0.1');

        request()->attributes->remove('livewire_rate_limit_checked');

        $this->expectException(TooManyRequestsHttpException::class);
        $this->expectExceptionMessage('Too many invalid Livewire requests');

        Checksum::verify($snapshot);
    }

    public function test_global_rate_limit_blocks_even_valid_requests()
    {
        // Pre-fill the global rate limiter past its threshold
        for ($i = 0; $i < 50; $i++) {
            RateLimiter::hit('livewire-checksum-failures:global', 60);
        }

        // Clear per-IP limiter so only global triggers
        RateLimiter::clear('livewire-checksum-failures:127.0.0.1');

        request()->attributes->remove('livewire_rate_limit_checked');

        $validSnapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
        ];
        $validSnapshot['checksum'] = Checksum::generate($validSnapshot);

        $this->expectException(TooManyRequestsHttpException::class);

        Checksum::verify($validSnapshot);
    }

    public function test_per_ip_limit_triggers_before_global_limit()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        // Hit the per-IP limit (5 failures)
        for ($i = 0; $i < 5; $i++) {
            request()->attributes->remove('livewire_rate_limit_checked');

            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        // Per-IP should be at 5, global should be at 5 (well under 50)
        $this->assertEquals(5, RateLimiter::attempts('livewire-checksum-failures:127.0.0.1'));
        $this->assertEquals(5, RateLimiter::attempts('livewire-checksum-failures:global'));

        // Next attempt should be blocked by per-IP limit
        request()->attributes->remove('livewire_rate_limit_checked');

        $this->expectException(TooManyRequestsHttpException::class);

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
