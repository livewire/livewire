<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

use function Livewire\trigger;

class Checksum {
    protected static $maxFailures = 10;
    protected static $decaySeconds = 600; // 10 minutes

    static function verify($snapshot) {
        // Check if this IP is already blocked due to too many failures
        static::enforceRateLimit();

        $checksum = $snapshot['checksum'];

        unset($snapshot['checksum']);

        trigger('checksum.verify', $checksum, $snapshot);

        if ($checksum !== $comparitor = self::generate($snapshot)) {
            trigger('checksum.fail', $checksum, $comparitor, $snapshot);

            static::recordFailure();

            throw new CorruptComponentPayloadException;
        }
    }

    protected static function enforceRateLimit()
    {
        $key = static::rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, static::$maxFailures)) {
            $seconds = RateLimiter::availableIn($key);

            throw new TooManyRequestsHttpException(
                $seconds,
                'Too many invalid Livewire requests. Please try again later.'
            );
        }
    }

    protected static function recordFailure()
    {
        RateLimiter::hit(static::rateLimitKey(), static::$decaySeconds);
    }

    protected static function rateLimitKey(): string
    {
        return 'livewire-checksum-failures:' . request()->ip();
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
