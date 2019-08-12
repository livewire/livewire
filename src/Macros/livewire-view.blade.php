@extends($layout)

@section($section)
    @foreach($components as $component => $componentOptions)
        @livewire($component, ...$componentOptions)
    @endforeach
@endsection
