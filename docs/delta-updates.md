After every update, Livewire sends the component's full server-rendered HTML back to the browser inside `effects.html`. The morphing system then updates only the DOM nodes that actually changed. That's great for the DOM, but the whole HTML string still went over the wire to get there — even when a single character changed.

The delta update engine trims that transport step. Instead of resending the entire render, the server diffs it against the component's previous render and sends only the bytes that changed. It's opt-in and global:

```php
// config/livewire.php

'update_engine' => 'delta',
```

You don't touch your components or Blade templates. The browser rebuilds the exact HTML from the patches and hands it to the same morph pipeline, so `wire:key`, focus, nested components, and morph hooks all behave exactly as before.

It's also hybrid: renders under 8 KiB skip the engine entirely and go through the normal morph transport. There's no point diffing a small component, so those updates never touch the render-state store or add any delta metadata to the response.

## Example

Here's a plain `counter`. Nothing about it is delta-aware:

```php
<?php // resources/views/components/⚡counter.blade.php

use Livewire\Component;

new class extends Component {
    public int $count = 0;

    public array $items = ['Alpha', 'Beta', 'Gamma'];

    public function increment()
    {
        $this->count++;
    }
};
?>

<div>
    <button wire:click="increment">Increment</button>

    <span>{{ $count }}</span>

    <ul>
        @foreach ($items as $index => $item)
            <li wire:key="item-{{ $index }}">{{ $item }}</li>
        @endforeach
    </ul>
</div>
```

Once the browser holds an exact baseline, bumping the count sends something like this instead of the whole render:

```json
{
    "htmlDelta": {
        "base": "8aef...",
        "patches": [
            { "start": 82, "delete": 1, "insert": "Mg==" }
        ]
    },
    "htmlHash": "b391..."
}
```

`start` and `delete` are UTF-8 byte offsets into the previous render, and `insert` is base64 so the patch stays valid JSON even when it lands in the middle of a multi-byte character.

The engine can also emit several patches at once. Moving a Kanban card is the usual example — you delete it from one column and insert it into another, and everything in between stays untouched. Every patch offset refers to the same original render, and the browser applies the whole list atomically or not at all.

Anchor discovery is bounded. If a render is almost entirely new, or a diff would need too many operations, the engine stops looking for anchors and just compares one big replacement patch against sending full HTML. Worst-case diff work stays predictable.

## Where the previous render lives

To build a delta, the server needs the exact render the browser is currently showing. After the first full update establishes that baseline, the engine keeps one render per active component in a cache store:

```php
// config/livewire.php

'delta' => [
    'store' => 'redis',
    'ttl' => 300,
    'minimum_html_bytes' => 8192,
    'minimum_savings' => 0.1,
    'minimum_compressed_savings_bytes' => 1024,
    'compression_aware' => true,
],
```

Leave `store` as `null` to use your default cache. A file cache is fine on a single server; once updates for the same component can hit different servers, you'll want a shared store like Redis.

Only the latest eligible render is kept, and anything under `minimum_html_bytes` never gets stored at all, which keeps cache usage in check for chatty components. If a request comes in against a render the server no longer has — say two updates raced — it just falls back to a full response rather than patching the wrong revision.

## Integrity and security

Delta updates change how HTML is shipped, not how requests are authorized. The browser never sends rendered HTML or patches for the server to apply — it only sends the hash of the render it currently has. The component ID and expected baseline hash live inside Livewire's checksum-protected snapshot, same as always.

Action parameters and public properties are still untrusted input. Validate and authorize them exactly as you would without the delta engine. See [Security](/docs/4.x/security) for the details.

The render gets verified at both ends before any delta is used:

1. The server re-hashes the cached render before diffing. If it doesn't match the expected hash, the entry is dropped and full HTML goes out.
2. The browser re-hashes the reconstructed HTML before morphing. If that doesn't match the server's target hash, it throws the baseline away and asks for a fresh render.

Verification on the client uses the Web Crypto API. If SHA-256 isn't available there, the browser simply stops advertising a baseline and everything continues on full HTML.

One thing worth keeping in mind: the cache holds raw rendered HTML, which can contain user-specific data. Keep the store on a private network, give it an app-specific prefix, lock it down with Redis ACLs and TLS where connections leave a trusted boundary, and don't set the TTL longer than the gap you'd expect between interactions.

Each successful delta costs one SHA-256 pass over the previous HTML on the server and one over the reconstructed HTML in the browser. There's no extra round trip, and next to rendering and transferring a full component it's usually cheap — though it does grow with unusually large renders.

## When it falls back to full HTML

Livewire sends the whole render whenever a delta isn't safe or isn't worth it:

- the browser doesn't have an exact baseline yet;
- the cached render expired or lives on another server;
- the cached render fails its integrity check, or the store is down;
- the browser and server baseline hashes disagree;
- the browser can't verify the result;
- the render is smaller than `minimum_html_bytes`;
- the delta wouldn't save enough — by default it has to clear the threshold twice, once on raw JSON and once on a quick gzip estimate, and beat `minimum_compressed_savings_bytes` after compression.

That double check matters because a base64 delta can look smaller before compression yet lose to highly repetitive HTML once gzip gets to it. Set `compression_aware` to `false` if your responses aren't compressed and you'd rather skip the extra probe — that also disables the compressed-savings threshold, since there's no longer an estimate to compare against.

Components can cross the size boundary in either direction. Grow past `minimum_html_bytes` and one full response re-establishes a baseline; shrink back under it and Livewire quietly returns to plain morphing and drops the client baseline.

The very first update after a page load is always full HTML. Browsers normalize parsed markup, so a component's `outerHTML` won't reliably match the server's exact string — that first response is what pins down a byte-exact baseline for later.

And if the client can't apply or verify a delta for any reason, it leaves the DOM as-is, discards its baseline, and requests a full render on its own.

## What it doesn't change

The delta engine only changes how a render reaches the browser, not how it's produced. PHP still renders your component on every normal update and the browser still morphs the DOM — you're just not resending the parts that didn't change. The snapshot travels in every request and response exactly as before.

If an interaction doesn't need a server render at all, reach for Alpine state or a renderless action instead. The delta engine is for the case where you *do* need the server to render but most of the output stays put.

## Configuration reference

| Option | Default | Description |
|---|---:|---|
| `update_engine` | `morph` | Set to `delta` to enable JSON render deltas globally |
| `delta.store` | Default cache store | Cache store used to hold the latest render |
| `delta.ttl` | `300` | Seconds an inactive render survives before expiring |
| `delta.minimum_html_bytes` | `8192` | Smallest render that's allowed to use the store at all |
| `delta.minimum_savings` | `0.1` | Fractional byte reduction a delta must reach before it's sent |
| `delta.minimum_compressed_savings_bytes` | `1024` | Absolute reduction required on the gzip estimate |
| `delta.compression_aware` | `true` | Also require the saving to hold up under a fast gzip estimate |
