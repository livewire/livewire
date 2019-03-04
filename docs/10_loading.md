# Loading States

Because Livewire makes a roundtrip to the server every time an action is triggered on the page, there are cases where the page may not react immediately to a user event (like a click). It is up to you to determine when you should provide the user with some kind of loading state or not.

Let's say we have a `Checkout` component that charges a user's credit card. Because something like processing a credit card can take a decent amount of time, it would make sense to provide the user with some kind of loading indicator.

Fortunately, Livewire makes this kind of thing simple. We can add a `wire:ref="checkout-button"` directive to the element that performs the action, and then reference that "ref" name in a loading directive attached to our loading element. Take a look below.

**view**
```html
<div>
    <input wire:model="cardNumber">
    <input wire:model="cvv">
    <button wire:click="checkout" wire:ref="checkout-button">Checkout</button>

    <div wire:loading="checkout-button">Processing Payment...</div>
</div>
```

Let's break down what's happening here:
* When the user clicks "Checkout", Livewire will look for any *wire:loading* directives that reference it's *wire:ref* and set it's CSS display property to whatever it was set at load.
