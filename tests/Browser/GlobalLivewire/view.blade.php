<div>

    <button wire:click="$set('output', 'foo')" dusk="foo">foo</button>

    <span dusk="output">{{ $output }}</span>
</div>

@push('scripts')
    <script>
        window.livewire.isLoaded = false
        window.livewire.onLoad(() => { window.isLoaded = true })
    </script>
@endpush
