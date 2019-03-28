# Loading States

Because Livewire makes a roundtrip to the server every time an action is triggered on the page, there are cases where the page may not react immediately to a user event (like a click). It is up to you to determine when you should provide the user with some kind of loading state or not.

Let's say we have a `Checkout` component that charges a user's credit card. Because something like processing a credit card can take a decent amount of time, it would make sense to provide the user with some kind of loading indicator.

Fortunately, Livewire makes this kind of thing simple. We can use the `wire:loading-class` directive to specify any classes we want added during the loading phase. If we want the inverse functionality (to remove a class), we can add the `.remove` modifier to the directive. For example:

**view**
```php
<div>
    <input wire:model.lazy="cardNumber">
    <button wire:click="checkout">Checkout</button>

    <div class="hidden" wire:loading-class.remove="hidden">
        Processing Payment...
    </div>
</div>
```

When the "Checkout" button is clicked, the "Processing Payment..." message will show. When the payment is finished processing, the message will dissapear.

## Targeting specific actions
The method outlined above works great for simple components, however, it's common to want to only show loading indicators for specific actions. Consider the following example:

**view**
```php
<div>
    <input wire:model.lazy="cardNumber">
    <button wire:click="checkout">Checkout</button>
    <button wire:click="cancel">Cancel</button>

    <div class="hidden" wire:loading-class.remove="hidden">
        Processing Payment...
    </div>
</div>
```

Notice, we've added a "Cancel" button to the checkout form. If the user clicks the "Cancel" button, the "Processing Payment..." message will show briefly. This is clearly undesireable, therefore Livewire offers two directives. You can add `wire:loading-target` the loading indicator, and pass in the name of a `ref` you define by attaching `wire:ref` to the target. Let's look at the adapted example:

**view**
```php
<div>
    <input wire:model.lazy="cardNumber">
    <button wire:click="checkout" wire:ref="checkout-button">Checkout</button>
    <button wire:click="cancel">Cancel</button>

    <div class="hidden" wire:loading-class.remove="hidden" wire:loading-target="checkout-button">
        Processing Payment...
    </div>
</div>
```

Now, when the "Checkout" button is clicked, the loading indicator will load, but not when the "Cancel" button is clicked.
