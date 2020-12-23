<div>

    <button wire:click="$set('output', 'foo')" dusk="foo">foo</button>

    <span dusk="output">{{ $output }}</span>
</div>

@push('scripts')
    <script>
        window.isLoaded = false
        window.loadEventWasFired = false
        window.livewire.onLoad(() => { window.isLoaded = true })
        document.addEventListener("livewire:load", function(event) {
            window.loadEventWasFired = true
        });
    </script>
@endpush
