<div>
    @php
        $model = $attributes->wire('model')->value();
    @endphp
    <span dusk="view.model">View: {{ $model }}</span>
    <input wire:model.live.hash="{{ $model }}" type="text" dusk="view.input" />
</div>
