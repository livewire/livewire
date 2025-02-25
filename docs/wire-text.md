
`wire:text` sets the text content of an element to the result of a given expression.

Here's a basic example of using `wire:text` to display a name:

```php
class ShowName extends Component
{
    public $name = 'Caleb';
}
```

```blade
<div>
    <p wire:text="name"></p>
</div>
```

Now the `<p>` tag's inner text content will be set to "Caleb".
