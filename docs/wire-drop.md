
`wire:drop` turns any element into a dropzone. It listens for things being dragged over the element, reflects the drag state so you can style an overlay, and evaluates its expression when something is dropped.

Unlike a plain `drop` event listener — which the browser never fires unless `dragover` is cancelled by hand — `wire:drop` manages the entire drag lifecycle for you.

## Basic usage

The most common dropzone accepts files, using the `.file` modifier together with the [`$upload` action](/docs/uploads#uploading-beyond-file-inputs) to upload the dropped files into a component property:

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
<div wire:drop.file="$upload('photos')">
    Drop photos here
</div>
```

The `.file` modifier scopes the dropzone to drags carrying files — dragging selected text across the element does nothing. Dropped files flow through Livewire's standard [file upload pipeline](/docs/uploads): temporary uploads, validation, previews, and [rich upload objects](/docs/uploads#rich-upload-objects-in-javascript) all work exactly as if the files came from a file input.

## Styling the drag state

While something is dragged over the dropzone, Livewire applies a `data-dragging` attribute to the element. Style it with plain CSS — no JavaScript required:

```blade
<div wire:drop.file="$upload('photos')" class="border-dashed data-dragging:border-blue-500">
    Drop photos here
</div>
```

Or target it from a descendant using Tailwind's `in-*` variants:

```blade
<div wire:drop.file="$upload('photos')">
    <div class="opacity-0 in-data-dragging:opacity-100">
        Release to upload
    </div>
</div>
```

With `.file`, the attribute only appears for drags carrying files, so overlays never flash while a user drags text around the page.

## Accepting drops anywhere on the page

Chat and editor interfaces often accept drops anywhere in the window rather than on one specific region. Add the `.window` modifier to listen at the window level:

```blade
<div wire:drop.file.window="$upload('attachments')">
    {{-- A full-screen overlay, shown while files are dragged over the page... --}}
    <div class="hidden in-data-dragging:grid fixed inset-0 place-items-center bg-black/50 text-white">
        Drop files to attach
    </div>

    {{-- ... --}}
</div>
```

The `data-dragging` attribute is still applied to the element carrying the directive, so overlays always have a styling anchor.

## General dropzones

Without `.file`, `wire:drop` is a general dropzone: any drag engages it, and any drop evaluates the expression — with the drop event available as `$event`. Use this for drops that aren't files, like text, links, or images dragged in from another tab:

```blade
<div wire:drop="importFromUrl($event.dataTransfer.getData('text/uri-list'))">
    Drop a link here
</div>
```

Two defaults worth knowing:

* Drops carrying files always have their browser default cancelled — nobody drops a file onto an app wanting the browser to navigate away and open it
* All other drops keep their default behavior (dropping text into an input still inserts it), unless your expression calls `$event.preventDefault()`

> [!tip] Uploading from a general dropzone still works
> `$upload` filters for itself — on a bare `wire:drop="$upload('photos')"` it uploads file drops and silently ignores everything else. Reach for `.file` when you want the *dropzone itself* scoped to files, so `data-dragging` (and your overlay) only engage for file drags.

## Reference

```blade
wire:drop="expression"
```

Modifier | Description
--- | ---
`.file` | Scope the dropzone to drags carrying files: only file drags show `data-dragging`, and only file drops evaluate the expression
`.window` | Accept drops anywhere on the page instead of only on this element
