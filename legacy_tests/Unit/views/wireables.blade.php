<div>
    <div>
        @if ($wireable)
            {{ $wireable->message }}

            @if ($wireable->embeddedWireable ?? false)
                {{ $wireable->embeddedWireable->message }}
            @endif
        @endif
    </div>
</div>
