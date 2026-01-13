<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Http\Response;

class StreamedResponse extends Response
{
    /**
     * Override to prevent header modification after streaming has started.
     */
    public function sendHeaders(?int $statusCode = null): static
    {
        return $this;
    }
}