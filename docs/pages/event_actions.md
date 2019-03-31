# Event Actions

Livewire currently offers a handful of directives to make listening to browser events trivial. The common format for all of them is: `wire:[event]="[action]"`.

Here are some common events you may need to listen for:

Event | Directive
--- | ---
click | `wire:click`
keydown | `wire:keydown`
submit | `wire:submit`

Here are a few examples of each in HTML:

**click**
```html
<button wire:click="showModal">Show Modal</button>
```

**keydown**
```html
<input wire:keydown.enter="search">
```

**submit**
```html
<form wire:submit="addTodo">
    <input wire:model="title">
    <button>Add Todo</button>
</form>
```

## Modifiers

Like you saw in the **keydown** example, Livewire directives sometimes offer "modifiers" to add extra functionality to an event. Below are the available modifiers that can be used with any event:

Modifier | Description
--- | ---
stop | Equivelant of `event.stopPropogation()`
prevent | Equivelant of `event.preventDefault()`

## Keydown Modifiers

To listen for specific keys on **keydown** events, you can pass the name of the key as a modifier. You can directly use any valid key names exposed via [KeyboardEvent.key](https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/key/Key_Values) as modifiers by converting them to kebab-case.

Here is a quick list of some common ones you may need:

Browser Event | Livewire Modifier
--- | ---
Backspace | backspace
Escape | escape
Shift | shift
Tab | tab
ArrowRight | arrow-right

```html
<input wire:keydown.page-down="someAction">
```

In the above example, the handler will only be called if `event.key` is equal to 'PageDown'.

## Special Actions
In Livewire, there are some "special" actions that are usually prefixed with a "$" symbol:

Function | Description
--- | ---
$set(_property_, _value_) | Shortcut to update the value of a property
$toggle(_property_) | Shortcut to toggle boolean properties on or off
$refresh | Will re-render the component without firing any action

You can pass these as the value of an event listener to do special things in Livewire.

Let's take `$set()` for example. It can be used to manually set a component property's value. Consider the `Counter` component's view.

**Before**
```php
<div>
    {{ $count }}
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
```

**After**
```php
<div>
    {{ $count }}
    <button wire:click="$set('count', {{ $count + 1 }})">+</button>
    <button wire:click="$set('count', {{ $count - 1 }})">-</button>
</div>
```

Notice that we are no longer calling the `increment` and `decrement` functions, we are direcly specifying, in blade, what we want data set to.

This can save on lots of redundant, one-line component methods that only exist to set, or toggle the value of component property.

## Polling Actions
Livewire offers a directive called `wire:interval="someAction"` that, when added to an element, will fire `someAction` to the Livewire component every `500ms`. You can customize the frequency by passing a directive modifier like `250ms`. For example:

```html
<div wire:interval.150ms="$refresh">
    Current time: {{ now() }}
</div>
```
