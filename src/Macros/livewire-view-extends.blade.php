@extends($view, $params)

@section($slotOrSection)
    <!-- Livewire Component wire-start:{{ $manager->initialDehydrate()->toInitialResponse()->fingerprint['id'] }} -->
    {!! $manager->initialDehydrate()->toInitialResponse()->effects['html'] !!}
    <!-- Livewire Component wire-end:{{ $manager->initialDehydrate()->toInitialResponse()->fingerprint['id'] }} -->
@endsection
