<div>
    @json($errors->toArray())

    @error('test') @enderror

    @component('dump-errors-nested-component')@endcomponent
</div>
