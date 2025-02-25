
Livewire has a `wire:show` directive to show and hide elements based on the result of an expression.

Here's an example of a component that toggles the visibility of a div based on the value of the `display` property.

```php
class ExtraContent extends Component
{
    public $display = false;
}
```

```blade
<div>
    <button wire:click="$toggle('display')">Toggle</button>
    <div wire:show="display">
        Contents...
    </div>
</div>
```

When the "Toggle" button is clicked, the `display` property is toggled, which causes the div to be shown or hidden.

## Important modifier

The `important` modifier can be added to the `wire:show` directive so the inline style on the element have precedence over any CSS styles that might be applied, ensuring the element is actually hidden.

```blade
<div>
    <button wire:click="$toggle('display')">Toggle</button>
    <div wire:show.important="display">
        Contents...
    </div>
</div>
```