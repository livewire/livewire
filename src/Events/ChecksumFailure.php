<?php

namespace Livewire\Events;

class ChecksumFailure
{
    public function __construct(
        public string $ipAddress,
        public string $userAgent,
        public ?string $componentName = null,
    ) {}
}
