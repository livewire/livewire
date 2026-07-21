<?php

namespace Livewire\Mechanisms\HandleRequests;

use Closure;
use Illuminate\Http\Request;
use Livewire\Exceptions\PayloadTooLargeException;
use Symfony\Component\HttpFoundation\InputBag;

class DecodeGzipRequests
{
    public const PAYLOAD_ATTRIBUTE = '_livewire_gzip_payload';

    public function handle(Request $request, Closure $next): mixed
    {
        // This middleware is registered globally and retained on the update
        // route as a fallback. Only decode once when both registrations run.
        if ($request->attributes->has(self::PAYLOAD_ATTRIBUTE)) {
            return $next($request);
        }

        if (! $request->hasHeader('X-Livewire') || ! $request->isJson()) {
            return $next($request);
        }

        $encoding = strtolower(trim((string) $request->header('Content-Encoding', '')));

        if ($encoding === '' || $encoding === 'identity') return $next($request);

        if ($encoding !== 'gzip') abort(415);

        $payload = $this->decodePayload($request);

        $request->attributes->set(self::PAYLOAD_ATTRIBUTE, true);
        $request->setJson(new InputBag($payload));

        return $next($request);
    }

    public function decodePayload(Request $request): array
    {
        if (! function_exists('gzdecode')) abort(415);

        $maximumBytes = config('livewire.payload.max_size');
        $maximumBytes = $maximumBytes === null
            ? null
            : max(0, (int) $maximumBytes);
        $content = $request->getContent();
        $actualContentLength = strlen($content);
        $declaredContentLength = filter_var(
            $request->header('Content-Length'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]],
        );
        $contentLength = max(
            $actualContentLength,
            $declaredContentLength === false ? 0 : $declaredContentLength,
        );

        if ($maximumBytes !== null && $contentLength > $maximumBytes) {
            throw new PayloadTooLargeException($contentLength, $maximumBytes);
        }

        $declaredDecodedLength = $this->decodedLength($content);

        if ($maximumBytes !== null
            && $declaredDecodedLength !== null
            && $declaredDecodedLength > $maximumBytes
        ) {
            throw new PayloadTooLargeException($declaredDecodedLength, $maximumBytes);
        }

        $decodeLimit = $maximumBytes === null || $maximumBytes === PHP_INT_MAX
            ? 0
            : $maximumBytes + 1;
        $decoded = @gzdecode($content, $decodeLimit);

        if (! is_string($decoded)) abort(400);

        if ($maximumBytes !== null && strlen($decoded) > $maximumBytes) {
            throw new PayloadTooLargeException(strlen($decoded), $maximumBytes);
        }

        try {
            $payload = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            abort(400);
        }

        if (! is_array($payload)) abort(400);

        return $payload;
    }

    protected function decodedLength(string $content): ?int
    {
        // RFC 1952 stores the uncompressed size modulo 2^32 in the trailer.
        if (strlen($content) < 18
            || substr($content, 0, 2) !== "\x1f\x8b"
            || ord($content[2]) !== 8
        ) {
            return null;
        }

        $trailer = unpack('Vlength', substr($content, -4));

        return is_array($trailer) ? $trailer['length'] : null;
    }
}
