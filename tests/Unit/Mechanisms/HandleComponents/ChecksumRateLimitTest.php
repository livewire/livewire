<?php

namespace Tests\Unit\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Mechanisms\HandleComponents\Checksum;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class ChecksumRateLimitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Clear any existing rate limits before each test
        RateLimiter::clear('livewire-checksum-failures:127.0.0.1');
    }

    public function test_checksum_failure_is_recorded()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component'],
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
            'memo' => ['name' => 'test-component'],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        for ($i = 0; $i < 5; $i++) {
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
            'memo' => ['name' => 'test-component'],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        // Hit the rate limit (10 failures)
        for ($i = 0; $i < 10; $i++) {
            try {
                Checksum::verify($snapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        // Next attempt should throw TooManyRequestsHttpException
        $this->expectException(TooManyRequestsHttpException::class);
        $this->expectExceptionMessage('Too many invalid Livewire requests');

        Checksum::verify($snapshot);
    }

    public function test_valid_checksum_does_not_record_failure()
    {
        $snapshot = [
            'memo' => ['name' => 'test-component'],
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
            'memo' => ['name' => 'test-component'],
            'data' => ['foo' => 'bar'],
            'checksum' => 'invalid-checksum',
        ];

        for ($i = 0; $i < 10; $i++) {
            try {
                Checksum::verify($invalidSnapshot);
            } catch (CorruptComponentPayloadException $e) {
                // Expected
            }
        }

        // Now try with a valid checksum - should still be blocked
        $validSnapshot = [
            'memo' => ['name' => 'test-component'],
            'data' => ['foo' => 'bar'],
        ];
        $validSnapshot['checksum'] = Checksum::generate($validSnapshot);

        $this->expectException(TooManyRequestsHttpException::class);

        Checksum::verify($validSnapshot);
    }
}
