
```blade
<form wire:submit="save">
    <label>
        <span>Title</span>

        <input type="text" wire:model="title">

        @error('title') <span>{{ $message }}</span> @enderror
    </label>

    <label>
        <span>Title</span>

        <input type="text" wire:model="title">

        @error('title') <span>{{ $message }}</span> @enderror
    </label>

    <button type="submit">Save</button>
</form>
```

```blade
<form wire:submit="save">
    <x-input-text label="Title" wire:model="title" :error="$error->first('title')" />

    <x-input-text label="Content" wire:model="content" :error="$error->first('content')" />

    <button type="submit">Save</button>
</form>
```

```blade
@props(['label', 'error'])

<label>
    <span>{{ $label }}</span>

    <input type="text" {{ $attributes->whereStartsWith('wire:') }}>

    @if($error) <span>{{ $error }}</span> @endif
</label>
```

## Injecting and running assets

```blade
@php $key = str()->uuid(); @endphp

<div>
    <input type="text" id="{{ $key }}">
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js" defer></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endassets

@script
<script>
    new Pikaday({ field: document.getElementById('{{ $key }}') });
</script>
@endscript
```
