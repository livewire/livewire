<div>
    <div>
        @if ($wireable)
            {{ $wireable->message }}

            @if ($wireable->embeddedWireable)
                {{ $wireable->embeddedWireable->message }}
            @endif
        @endif
    </div>
</div>
