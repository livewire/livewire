<div>
    <button type="button" wire:click="setOutputToFooHeader" dusk="foo">Foo</button>

    <p>
        <span dusk="output">{{ $output }}</span>
    </p>
    <p>
        <span dusk="altoutput">{{ $altoutput }}</span>
    </p>
</div>

@push('scripts')
    <script type="text/javascript">
        document.addEventListener("livewire:load", function(event) {
            window.livewire.addHeaders({
                'X-Foo-Header': 'Bar'
            });
            window.livewire.addHeaders({
                'X-Bazz-Header': 'Bazz'
            });
        });
    </script>
@endpush
