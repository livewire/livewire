## Tag syntax

Blade components are currently rendered using the following syntax:

```php
<x-project.create />
<x-pages::project.create />
<x-layouts::project.create />
```

Livewire v3 components are rendered using the following syntax:

```php
<livewire:project.create />
<livewire:pages::project.create />
<livewire:layouts::project.create />
```

I think I want Livewire v4 components to be rendered using the following syntax:

```php
<wire:project.create />
<wire:pages::project.create />
<wire:layouts::project.create />
```

It's so much cleaner to me.

These `wire:` components now should map to the `resources/views/components` directory...
