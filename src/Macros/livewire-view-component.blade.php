@component($view, $params)
    @slot($slotOrSection)
        {!! $manager->initialDehydrate()->toInitialResponse()->effects['html']; !!}
    @endslot
@endcomponent
