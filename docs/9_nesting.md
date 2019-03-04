# Nesting Components

Similar to other component-driven front-end frameworks, Livewire components can be nested. Composing components is an incredibly powerful architectural feature, however, it comes with some costs, which I will be sure to address later on.

Nesting components is as simple and straightforward as you'd hope. Let's assume the Blade view below belongs to a component called `ShoppingCart`. This component shows a a list of products in your cart, but the checkout form is handled by a child component called `CheckoutForm`.

**livewire/shopping-cart.blade.php**
```html
<div>
    <ul>
        @foreach ($cart->products as $product)
            <li>{{ $product->id }}</li>
        @endforeach
    </ul>

    <button wire:click="showCheckoutForm">Checkout</button>

    @if ($showCheckout)
        @livewire('checkout-form', $cart)
    @endif
</div>
```

**ShoppingCart.php**
```php
class ShoppingCart extends LivewireComponent
{
    public $cart;
    public $showCheckout = false;

    [...]

    public function showCheckoutForm()
    {
        $this->showCheckout = true;
    }

    public function render()
    {
        return view('livewire.shopping-cart');
    }
}
```

Now, when a user clicks on the "Checkout" button, the child component `CheckoutForm` will be visible and the `$cart` object will be passed in. Here is what the child component would look like:

**CheckoutForm.php**
```php
class CheckoutForm extends LivewireComponent
{
    public $cart;

    public function created($cart)
    {
        $this->cart = $cart;
    }

    [...]
}
```

Now, we've covered rendering a child component and passing data down into it. But what about communicating up from the child to the parent component? For this Livewire offers a simple event emission system similar to Vue's. Let's take a look:

Let's imagine the `CheckoutForm` component has a "cancel" button that communicates to the parent to hide the checkout form. Before we continue, let's prepare the `ShoppingCart` to listen for a "cancel" event from the child.

First, we need to register an event listener for the "cancel" event. These listeners are straightforward, they expect you to pass them an event name, and the method you want to be called when the event fires.

**livewire/shopping-cart.blade.php**
```html
<div>
    [...]

    @if ($showCheckout)
        @livewire('checkout-form', $cart)
            @on('cancel', 'hideCheckoutForm')
        @endlivewire
    @endif
</div>
```

Now that we've mapped the "cancel" event to the "hideCheckoutForm" method, let's add the "hideCheckoutForm" method to our parent component.

**ShoppingCart.php**
```php
class ShoppingCart extends LivewireComponent
{
    [...]

    public function hideCheckoutForm()
    {
        $this->showCheckout = false;
    }

    [...]
}
```

Now that our parent is set up to handle the "cancel" event, let's implement that cancel event emission in our child component.

There are 2 ways to emit an event in a component: from the view, and from the component class. I'll demonstrate both, but always prefer the "from the view" approach as it's more performant.

### From The View
**livewire/checkout-form.blade.php**
```html
<div>
    [...]

    <button wire:click="$emit('cancel')">Cancel</button>

    [...]
</div>
```

### From The Component Class

Imagine we want to cancel the checkout process if the user's payment doesn't process. We might have a method in `CheckoutForm` called `checkout` that handles this logic.

**CheckoutForm.php**
```php
class CheckoutForm extends LivewireComponent
{
    [...]

    public function checkout()
    {
        if ($this->card->charge($this->amount)) {
            $this->redirect('/cart/success');
        } else {
            $this->emit('cancel');
        }
    }

    [...]
}
```

## Gotchas, Cavaets, and Tips
* Contrary to what you might think, components can actually make your application faster. Breaking a large component up into smaller ones means there is less information that has to passed back and forth to the server after each action.
* Always prefer the `wire:X="$emit('X')" syntax to the `$this->emit('X')` syntax becuase the former only triggers one network request, where the latter requires two.
* If you are used to front-end frameworks like VueJs, you might be used to creating very small utility components for things like `<input>` elements. Livewire is not meant for things like that. In those cases, you should use Blade components and includes. They often better suited for those tasks anyway.
