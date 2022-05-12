<div>
    <input wire:model="search" dusk="input" />

    <p dusk="request">Request:{{ request('search') ?: '#' }}</p>
    <p dusk="search">Search:{{ $search ?: '#' }}</p>
</div>
