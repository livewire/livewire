@extends($view, $params)

@section($slotOrSection)
    {!! $manager->initialDehydrate()->toInitialResponse()->effects['html'] !!}
    <!-- Livewire Component wire-end:{{ $manager->initialDehydrate()->toInitialResponse()->fingerprint['id'] }} -->
@endsection
