<div>
    <!-- It's important that this element has not server-side HTML side-effects so we can prove an empty HTML payload won't cause Alpine issues. -->
    <div x-data>
        <h1 x-text="$wire.count" dusk="output"></h1>

        <button x-on:click="$wire.increment()" dusk="button">$wire.increment()</button>
    </div>
</div>
