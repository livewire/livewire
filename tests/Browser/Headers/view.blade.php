<div>
    <button type="button" wire:click="setOutputToFooHeader" dusk="foo">Foo</button>

    <span dusk="output">{{ $output }}</span>
</div>

@push('scripts')
    <script type="text/javascript">
        document.addEventListener("livewire:load", function(event) {
            window.livewire.connection.headers = {
                'X-Foo-Header': 'Bar'
            }
        });
    </script>
@endpush
