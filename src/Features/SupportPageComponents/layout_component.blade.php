@component(LegacyTests\AppLayout::class, $layout['params'])
    @slot($layout['slotOrSection'])
        {!! $content !!}
    @endslot
@endcomponentClass
