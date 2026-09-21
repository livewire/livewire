<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

use function Livewire\trigger;

class Checksum {
    protected static $maxFailures = 10;
    protected static $decaySeconds = 600; // 10 minutes
    protected static $rateLimitingEnabledForTesting = false;
    protected static $rateLimitKeyResolver = null;

    static function verify($snapshot) {
        // Check if this client is already blocked due to too many failures
        static::enforceRateLimit();

        $checksum = $snapshot['checksum'];

        unset($snapshot['checksum']);

        trigger('checksum.verify', $checksum, $snapshot);

        if (! hash_equals($comparitor = self::generate($snapshot), $checksum)) {
            trigger('checksum.fail', $checksum, $comparitor, $snapshot);

            static::recordFailure();

            throw new CorruptComponentPayloadException;
        }
    }

    static function enableRateLimitingForTesting()
    {
        static::$rateLimitingEnabledForTesting = true;
    }

    static function disableRateLimitingForTesting()
    {
        static::$rateLimitingEnabledForTesting = false;
    }

    static function setRateLimitKey($callback)
    {
        static::$rateLimitKeyResolver = $callback;
    }

    protected static function enforceRateLimit()
    {
        if (! static::rateLimitingEnabled()) return;

        $request = request();

        // Only check the rate limit once per request (not once per component)
        if ($request->attributes->get('livewire_rate_limit_checked')) {
            return;
        }

        $key = static::rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, static::maxFailures())) {
            $seconds = RateLimiter::availableIn($key);

            throw new TooManyRequestsHttpException(
                $seconds,
                'Too many invalid Livewire requests. Please try again later.'
            );
        }

        $request->attributes->set('livewire_rate_limit_checked', true);
    }

    protected static function recordFailure()
    {
        if (! static::rateLimitingEnabled()) return;

        RateLimiter::hit(static::rateLimitKey(), static::decaySeconds());
    }

    protected static function rateLimitingEnabled(): bool
    {
        if (app()->runningUnitTests() && ! static::$rateLimitingEnabledForTesting) return false;

        return static::maxFailures() > 0;
    }

    protected static function maxFailures(): ?int
    {
        return config('livewire.checksum_rate_limit.max_failures', static::$maxFailures);
    }

    protected static function decaySeconds(): int
    {
        return config('livewire.checksum_rate_limit.decay_seconds') ?? static::$decaySeconds;
    }

    protected static function rateLimitKey(): string
    {
        $request = request();

        $key = static::$rateLimitKeyResolver
            ? (string) (static::$rateLimitKeyResolver)($request)
            : '';

        // Custom keys are namespaced so they can never collide with an IP address...
        return $key === ''
            ? 'livewire-checksum-failures:' . $request->ip()
            : 'livewire-checksum-failures:key:' . $key;
    }

    static function generate($snapshot) {
        $hashKey = app('encrypter')->getKey();

        // Remove the children from the memo in the snapshot, as it is actually Ok
        // if the "children" tracking is tampered with. This way JavaScript can
        // modify children as it needs to for dom-diffing purposes...
        unset($snapshot['memo']['children']);
        
        $checksum = hash_hmac('sha256', json_encode($snapshot), $hashKey);

        trigger('checksum.generate', $checksum, $snapshot);

        return $checksum;
    }
}
