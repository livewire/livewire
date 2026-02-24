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

        // Hit the rate limit (10 failures across 10 "requests")
        for ($i = 0; $i < 10; $i++) {
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

        for ($i = 0; $i < 10; $i++) {
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

        // Second "request" - verify again
        Checksum::verify($snapshot);

        // tooManyAttempts should be called twice (once per request)
        RateLimiter::shouldHaveReceived('tooManyAttempts')->twice();
    }

    public function test_debug_mode_includes_first_payload_difference_on_checksum_failure()
    {
        config()->set('app.debug', true);

        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
        ];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        $tamperedSnapshot = $snapshot;
        $tamperedSnapshot['data']['foo'] = 'baz';

        request()->attributes->remove('livewire_rate_limit_checked');

        try {
            Checksum::verify($tamperedSnapshot);
            $this->fail('Expected CorruptComponentPayloadException to be thrown.');
        } catch (CorruptComponentPayloadException $e) {
            $this->assertStringContainsString('Checksum mismatch details:', $e->getMessage());
            $this->assertStringContainsString('First differing path [data.foo]', $e->getMessage());
            $this->assertStringContainsString('expected "bar" and received "baz"', $e->getMessage());
        }
    }

    public function test_debug_details_are_not_included_when_debug_mode_is_disabled()
    {
        config()->set('app.debug', false);

        $snapshot = [
            'memo' => ['name' => 'test-component', 'release' => ReleaseToken::generate(ChecksumRateLimitTestComponent::class)],
            'data' => ['foo' => 'bar'],
        ];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        $tamperedSnapshot = $snapshot;
        $tamperedSnapshot['data']['foo'] = 'baz';

        request()->attributes->remove('livewire_rate_limit_checked');

        try {
            Checksum::verify($tamperedSnapshot);
            $this->fail('Expected CorruptComponentPayloadException to be thrown.');
        } catch (CorruptComponentPayloadException $e) {
            $this->assertStringNotContainsString('Checksum mismatch details:', $e->getMessage());
        }
    }
}

class ChecksumRateLimitTestComponent extends Component
{
    public function render()
    {
        return '<div></div>';
    }
}
