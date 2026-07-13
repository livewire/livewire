
`wire:drop` turns any element into a dropzone. It listens for files being dragged over the element, reflects the drag state so you can style an overlay, and evaluates its expression when files are dropped.

Unlike a plain `drop` event listener — which the browser never fires unless `dragover` is cancelled by hand — `wire:drop` manages the entire drag lifecycle for you. It also only engages with drags that carry files: dragging selected text across the element does nothing.

## Basic usage

The most common expression is the [`$upload` action](/docs/uploads#uploading-beyond-file-inputs), which uploads the dropped files into a component property:

```php
<?php // resources/views/components/⚡photo-gallery.blade.php

use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Livewire\Component;

new class extends Component {
    use WithFileUploads;

    #[Validate(['photos.*' => 'image|max:10240'])]
    public $photos = [];
};
```

```blade
<div wire:drop="$upload('photos')">
    Drop photos here
</div>
```

Dropped files flow through Livewire's standard [file upload pipeline](/docs/uploads) — temporary uploads, validation, previews, and [rich upload objects](/docs/uploads#rich-upload-objects-in-javascript) all work exactly as if the files came from a file input.

The expression is an ordinary action expression, so you can also call your own JavaScript with the drop event available as `$event`:

```blade
<div wire:drop="handleDrop($event.dataTransfer.files)">
    Drop files here
</div>
```

## Styling the drag state

While files are dragged over the dropzone, Livewire applies a `data-dragging` attribute to the element. Style it with plain CSS — no JavaScript required:

```blade
<div wire:drop="$upload('photos')" class="border-dashed data-dragging:border-blue-500">
    Drop photos here
</div>
```

Or target it from a descendant using Tailwind's `in-*` variants:

```blade
<div wire:drop="$upload('photos')">
    <div class="opacity-0 in-data-dragging:opacity-100">
        Release to upload
    </div>
</div>
```

## Accepting drops anywhere on the page

Chat and editor interfaces often accept drops anywhere in the window rather than on one specific region. Add the `.window` modifier to listen at the window level:

```blade
<div wire:drop.window="$upload('attachments')">
    {{-- A full-screen overlay, shown while files are dragged over the page... --}}
    <div class="hidden in-data-dragging:grid fixed inset-0 place-items-center bg-black/50 text-white">
        Drop files to attach
    </div>

    {{-- ... --}}
</div>
```

The `data-dragging` attribute is still applied to the element carrying the directive, so overlays always have a styling anchor.

## Reference

```blade
wire:drop="expression"
```

Modifier | Description
--- | ---
`.window` | Accept drops anywhere on the page instead of only on this element
