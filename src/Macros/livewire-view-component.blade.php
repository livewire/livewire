@component($view, $params)
    @slot($slotOrSection)
        {!! $manager->initialDehydrate()->toInitialResponse()->effects['html'] !!}
        <!-- Livewire Component wire-end:{{ $manager->initialDehydrate()->toInitialResponse()->fingerprint['id'] }} -->
    @endslot
@endcomponent
