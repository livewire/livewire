<div>
    <input wire:model.defer="filters.search" type="text" dusk="search">
    <input wire:model.defer="filters.status" type="text" dusk="status">
    <button wire:click.prevent="$refresh" dusk="submit">Submit</button>
    <button type="button" wire:click='doNotCreateHistoryWhenEmptyArray' dusk="empty-array-btn">
        Empty Array
    </button>
    <span dusk="empty-array-output">{{ $notCreateHistoryWhenEmptyArrayMessage }}</span>
    <button type="button" wire:click='doNotCreateHistoryWhenArrayHasSameContent' dusk="same-content-array-btn">
        Query String Is Not Changed
    </button>
    <span dusk="same-content-array-output">{{ $notCreateHistoryWhenArrayHasSameContentMessage }}</span>
</div>