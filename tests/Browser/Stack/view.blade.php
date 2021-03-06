<div>
    <div>foo</div>

    @if ($active)
    <div>bar</div>
    @endif
</div>

@push('page_bottom')
<button dusk="toggle" wire:click="$toggle('active')">Toggle active</button>

<button dusk="toggleChild" wire:click="$toggle('showChild')">Toggle child</button>

@if ($active)
<div>baz</div>
@endif

@if ($showChild)
@livewire(Tests\Browser\Stack\NestedComponent::class)
@endif
@endpush
