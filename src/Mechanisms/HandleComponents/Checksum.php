<?php

namespace Livewire\Mechanisms\HandleComponents;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

use function Livewire\trigger;

class Checksum {
    protected static $maxFailures = 10;
    protected static $decaySeconds = 600; // 10 minutes
    protected static $debugSnapshotTtl = 600; // 10 minutes

    static function verify($snapshot) {
        // Check if this IP is already blocked due to too many failures
        static::enforceRateLimit();

        $checksum = $snapshot['checksum'];

        unset($snapshot['checksum']);

        trigger('checksum.verify', $checksum, $snapshot);

        if (! hash_equals($comparitor = self::generate($snapshot, false), $checksum)) {
            trigger('checksum.fail', $checksum, $comparitor, $snapshot);

            static::recordFailure();

            throw new CorruptComponentPayloadException(
                static::debugChecksumFailureContext($checksum, $comparitor, $snapshot)
            );
        }
    }

    protected static function enforceRateLimit()
    {
        $request = request();

        // Only check the rate limit once per request (not once per component)
        if ($request->attributes->get('livewire_rate_limit_checked')) {
            return;
        }

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
    }

    protected static function rateLimitKey(): string
    {
        return 'livewire-checksum-failures:' . request()->ip();
    }

    static function generate($snapshot, bool $storeForDebugging = true) {
        $hashKey = app('encrypter')->getKey();

        // Remove the children from the memo in the snapshot, as it is actually Ok
        // if the "children" tracking is tampered with. This way JavaScript can
        // modify children as it needs to for dom-diffing purposes...
        unset($snapshot['memo']['children']);

        $checksum = hash_hmac('sha256', json_encode($snapshot), $hashKey);

        if ($storeForDebugging) {
            static::storeSnapshotForDebugging($checksum, $snapshot);
        }

        trigger('checksum.generate', $checksum, $snapshot);

        return $checksum;
    }

    protected static function storeSnapshotForDebugging(string $checksum, array $snapshot): void
    {
        if (! config('app.debug')) return;

        Cache::put(
            static::debugSnapshotCacheKey($checksum),
            $snapshot,
            now()->addSeconds(static::$debugSnapshotTtl)
        );
    }

    protected static function debugChecksumFailureContext(string $checksum, string $comparitor, array $tamperedSnapshot): ?string
    {
        if (! config('app.debug')) return null;

        $canonicalSnapshot = Cache::get(static::debugSnapshotCacheKey($checksum));

        if (! is_array($canonicalSnapshot)) {
            return "Checksum mismatch details: received [{$checksum}] but computed [{$comparitor}]. No baseline snapshot was found for this checksum.";
        }

        [ $path, $expected, $actual ] = static::firstDiff($canonicalSnapshot, $tamperedSnapshot);

        if ($path === null) {
            return "Checksum mismatch details: received [{$checksum}] but computed [{$comparitor}]. No specific payload diff could be determined.";
        }

        return "Checksum mismatch details: received [{$checksum}] but computed [{$comparitor}]. "
            ."First differing path [{$path}] expected ".static::stringifyDebugValue($expected)
            ." and received ".static::stringifyDebugValue($actual).".";
    }

    protected static function debugSnapshotCacheKey(string $checksum): string
    {
        return "livewire:checksum-snapshot:{$checksum}";
    }

    protected static function firstDiff(array $expected, array $actual, string $path = ''): array
    {
        $keys = array_values(array_unique(array_merge(array_keys($expected), array_keys($actual))));

        foreach ($keys as $key) {
            $hasExpected = array_key_exists($key, $expected);
            $hasActual = array_key_exists($key, $actual);
            $currentPath = $path === '' ? (string) $key : $path.'.'.$key;

            if (! $hasExpected) return [$currentPath, null, $actual[$key]];
            if (! $hasActual) return [$currentPath, $expected[$key], null];

            $expectedValue = $expected[$key];
            $actualValue = $actual[$key];

            if (is_array($expectedValue) && is_array($actualValue)) {
                [ $diffPath, $diffExpected, $diffActual ] = static::firstDiff($expectedValue, $actualValue, $currentPath);

                if ($diffPath !== null) {
                    return [ $diffPath, $diffExpected, $diffActual ];
                }

                continue;
            }

            if ($expectedValue !== $actualValue) {
                return [ $currentPath, $expectedValue, $actualValue ];
            }
        }

        return [null, null, null];
    }

    protected static function stringifyDebugValue($value): string
    {
        $encoded = json_encode($value);

        if ($encoded === false) return '"[unserializable]"';

        if (strlen($encoded) > 120) {
            return substr($encoded, 0, 117).'...';
        }

        return $encoded;
    }
}
