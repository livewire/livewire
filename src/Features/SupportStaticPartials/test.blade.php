@props(['count'])

@static
<div>
    <h2 dusk="nested-static-1">{{ $count }}</h2>

    @staticSlot
        <h3 dusk="nested-static-2">{{ $count }}</h3>
    @endstaticSlot

    <h4 dusk="nested-static-3">{{ $count }}</h4>

    @staticSlot
        <h5 dusk="nested-static-4">{{ $count }}</h5>
    @endstaticSlot
</div>
@endstatic
