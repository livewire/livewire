# Event Listeners

Livewire currently offers a handful of directives to make listening to browser events trivial. The comon format for all of them is: `wire:[browser event]="[component method]"`.

Here are the event directives currently implemented in Livewire:

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
min.Xms | Set a minimum loading time in milliseconds. This is useful for when you are showing a loading wheel for an action, and don't want it to dissapear too quickly, so the user feels like something important is processing.


## Keydown Modifiers

To listen for specific keys on **keydown** events, you can pass the name of the key as a modifier. You can directly use any valid key names exposed via [KeyboardEvent.key](https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/key/Key_Values) as modifiers by converting them to kebab-case.

Here is a quick list of some common ones that are available:

Key |
--- |
enter |
backspace |
escape |
super |
shift |
alt |
tab |
arrow-down |
arrow-left |
arrow-right |
arrow-up |

```html
<input wire:keydown.page-down="onPageDown">
```

In the above example, the handler will only be called if `event.key` is equal to 'PageDown'.

## Special functions
In Livewire, there are some "special" functions usually prefixed with a "$" symbol:

Function | Description
--- | ---
$set(_prop_, _value_) | Shortcut to update the value of a prop
$emit(_event_, _param_) | Emit an event on the parent component
