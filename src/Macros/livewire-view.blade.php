@extends($layout, $layout_params ?: [])

@section($section)
    @livewire($component, $componentParameters)
@endsection
