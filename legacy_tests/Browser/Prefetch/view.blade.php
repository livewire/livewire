<div>
    <button wire:click.prefetch="$refresh" dusk="button">inc</button>

    <span dusk="count">{{ app('session')->get('count') }}</span>
</div>
