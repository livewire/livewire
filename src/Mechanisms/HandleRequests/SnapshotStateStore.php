<?php

namespace Livewire\Mechanisms\HandleRequests;

interface SnapshotStateStore
{
    public function get(string $reference, string $componentId): ?string;

    public function put(string $componentId, string $snapshot): ?string;
}
