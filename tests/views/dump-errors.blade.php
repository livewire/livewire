<div>
    @json($errors->toArray())

    @error('test') @enderror

    @component('components.dump-errors-nested-component')@endcomponent
</div>
