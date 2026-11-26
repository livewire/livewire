@island
    <div>
        <span dusk="{{ $label }}-count">{{ $label }}: {{ $count }}</span>
        <button type="button" wire:click="increment" dusk="{{ $label }}-increment">Increment</button>
    </div>
@endisland
