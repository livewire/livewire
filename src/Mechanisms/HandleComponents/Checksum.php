<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

use function Livewire\trigger;

class Checksum {
    protected static $maxFailures = 5;
    protected static $decaySeconds = 600; // 10 minutes

    protected static $maxGlobalFailures = 50;
    protected static $globalDecaySeconds = 60; // 1 minute

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
        $request = request();

        // Only check the rate limit once per request (not once per component)
        if ($request->attributes->get('livewire_rate_limit_checked')) {
            return;
        }

        // Check global rate limit first — this catches distributed attacks
        // using IP rotation where no single IP exceeds its individual limit.
        $globalKey = static::globalRateLimitKey();

        if (RateLimiter::tooManyAttempts($globalKey, static::$maxGlobalFailures)) {
            $seconds = RateLimiter::availableIn($globalKey);

            throw new TooManyRequestsHttpException(
                $seconds,
                'Too many invalid Livewire requests. Please try again later.'
            );
        }

        // Check per-IP rate limit.
        //
        // Note: request()->ip() relies on trusted proxy configuration. If your
        // application sits behind a reverse proxy or load balancer, you must
        // configure trusted proxies in App\Http\Middleware\TrustProxies (or
        // the TRUSTED_PROXIES environment variable) so that request()->ip()
        // returns the real client IP rather than the proxy IP. Without this,
        // all requests appear to come from the same IP, which will cause
        // legitimate users to be rate-limited after only a few failures from
        // any client behind that proxy.
        //
        // See: https://laravel.com/docs/requests#configuring-trusted-proxies
        $key = static::rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, static::$maxFailures)) {
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
        RateLimiter::hit(static::rateLimitKey(), static::$decaySeconds);
        RateLimiter::hit(static::globalRateLimitKey(), static::$globalDecaySeconds);
    }

    protected static function rateLimitKey(): string
    {
        return 'livewire-checksum-failures:' . request()->ip();
    }

    protected static function globalRateLimitKey(): string
    {
        return 'livewire-checksum-failures:global';
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
