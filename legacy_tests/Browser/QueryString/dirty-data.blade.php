<div>
    <button wire:click="nextPage" dusk="nextPage">Next Page</button>

    @if ($page === 1)
        <div>
            <input type="text" wire:model.live="foo.bar" dusk="input">
        </div>
    @else
        <div>
            The Next Page
        </div>
    @endif
</div>
