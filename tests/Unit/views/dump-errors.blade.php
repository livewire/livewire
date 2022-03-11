<div>
    @json($errors->getBag($bag ?? 'default')->toArray())

    @error('test', $bag ?? 'default') @enderror

    @component('dump-errors-nested-component')@endcomponent
</div>
