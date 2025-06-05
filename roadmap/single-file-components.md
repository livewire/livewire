
## Syntax

```php
@php
new Livewire\Component {
    //
}
@endphp

<!-- Or -->

@php(new App\Livewire\Project\Create)

<div>
    <!-- ... -->
</div>

<script>
    // ...
</script>
```

## Systems:
* A unified registration/lookup system


@todo:
* explore how this would feel for a few flux components

```php
@php
new class extends Blade\Component {
    public string $type = '';
    public string $message = '';

    public function mount()
    {
        //
    }
}
@endphp

<div>
    Count: {{ $count }}
</div>
```
