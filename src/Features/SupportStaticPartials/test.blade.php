@props(['count'])

@static
<div>
    <h2 dusk="static-1">foo</h2>

    @dynamic
        <h3 dusk="dynamic-1">{{ $count }}</h3>
    @enddynamic

    <h4 dusk="static-2">bar</h4>

    @dynamic
        <h5 dusk="dynamic-2">{{ $count }}</h5>
    @enddynamic
</div>
@endstatic
