<div>
    <button wire:click="show" dusk="button"></button>
    @if($withScript)
        <script>window.scriptTagWasCalled = true</script>
    @endif
</div>
