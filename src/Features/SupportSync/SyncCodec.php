<?php

namespace Livewire\Features\SupportSync;

interface SyncCodec
{
    public function toLivewire(mixed $value): mixed;

    public function fromLivewire(mixed $value): mixed;
}
