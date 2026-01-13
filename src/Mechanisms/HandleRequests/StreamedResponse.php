<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Http\Response;

/**
 * A specialized response class for handling Livewire streaming responses.
 *
 * When wire:stream is used, headers are sent early by SupportStreaming::ensureStreamResponseStarted().
 * The streaming content has already been output via echo/flush in SupportStreaming::streamContent().
 * This final JSON response contains the component snapshot and must be output without attempting
 * to send additional headers, which would cause "headers already sent" warnings (since Symfony 7.2.7).
 *
 * This class prevents header modification attempts by overriding the sendHeaders() method to be a no-op.
 * The headers are still set on the response object for consistency and debugging, but they won't be sent.
 *
 * @see \Livewire\Features\SupportStreaming\SupportStreaming::ensureStreamResponseStarted()
 * @see https://github.com/symfony/symfony/issues/60603
 * @see https://github.com/livewire/livewire/issues/9357
 */
class StreamedResponse extends Response
{
    /**
     * Override to prevent header modification after streaming has started.
     *
     * This method is called by Laravel's response sending pipeline, but since
     * headers have already been sent during the streaming phase, we skip the
     * actual header transmission to avoid PHP warnings.
     *
     * @param int|null $statusCode The HTTP status code (ignored in streaming context)
     * @return static
     */
    public function sendHeaders(?int $statusCode = null): static
    {
        return $this;
    }
}
