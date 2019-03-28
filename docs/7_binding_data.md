# Data Binding
If you've used front-end frameworks like Angular, React, or Vue, you are already familiar with this concept. However, if you are new to this concept, it's fancy name my intimidate you. Fear not, it's actually quite straightforward.

Consider the following Livewire component:

```php
class FavoriteColor extends LivewireComponent
{
    public $color;

    public function render()
    {
        return view('livewire.favorite-color');
    }
}
```

**favorite-color.blade.php**
```html
<div>
    <input wire:model="color">
    <button wire:click="storeColor">Store Favorite Color</button>
</div>
```

When the `<button>` is clicked, the `storeColor` method will fire in the component, but before it fires, the `$color` property will be set to the current value in the `<input>` field.

## Live updating

By default, Livewire "lazilly updates" the value of `$color`. In other words, it doesn't send update the value everytime the user types in the `<input>` field, only when the user performs a Livewire action on the page, in this case, that means clicking the `<button>` element.

If for some reason, this isn't desirable, for instance if you are doing real-time validation on an input element or live-updating search results, you can add the `live` modifier to the `wire:model` attribute.

```php
<div>
    <input wire:model.live="search">

    @if ($errors->has('search'))
        <span>{{ $errors->first('search')</span>
    @endif
</div>
```

Now, everytime the user types into the `<input>` field, the `$search` property on the Livewire component will be updated.
