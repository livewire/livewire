# Livewire Context System Deep Dive

## Overview
The context system in Livewire is a mechanism for attaching metadata to actions and messages as they flow through the request pipeline. There are two distinct but related types of context:

1. **Action Context** - Metadata attached to individual actions (sent to server)
2. **Message Context** - Temporary metadata for attaching DOM elements to actions

## The Two Context Flows

### 1. Action Context (Persistent)
This is metadata that gets attached to an action and **sent to the server**. It includes information like:
- `type`: The type of action ('user', 'poll', 'refresh')
- `island`: Island-specific metadata for island requests
- Any custom metadata needed by the server

**Flow:**
```
Action created → addContext() → stored in action.context → sent to server in payload
```

### 2. Message Context (Temporary)
This is metadata used to **connect DOM elements to disconnected actions**. It includes:
- `el`: The originating DOM element
- `directive`: The wire: directive that triggered the action

**Flow:**
```
addContext(component, {el, directive}) → stored in MessageBroker → pulled by Action.fire() → attached to Action
```

## How It Actually Works

### Step 1: Context is Added to MessageBroker
When a directive like `wire:model` triggers an action:

```javascript
// wire-model.js
addContext(component, {
    el,        // The input element
    directive  // The wire:model directive
})
```

This stores the context in the MessageBroker's message for that component.

### Step 2: Action Pulls Context When Fired
When an action is fired:

```javascript
// action.js
fire() {
    // Pull any pending context from the message broker
    let context = messageBroker.pullContext(this.component)

    // Extract el and directive separately (they're special)
    if (context.el) {
        this.el = context.el
        delete context.el
    }
    if (context.directive) {
        this.directive = context.directive
        delete context.directive
    }

    // Merge remaining context into action context
    this.addContext(context)

    // Fire interceptors with access to el, directive, and context
    this.interceptorRegistry.fire(this)
}
```

### Step 3: Interceptors Have Access to Everything
Interceptors receive the action with:
- `action.el` - The originating element
- `action.directive` - The originating directive
- `action.context` - All other metadata

```javascript
// supportDataLoading.js
intercept(({ action, component, request, el, directive }) => {
    if (!el) return  // No element means programmatic call

    // Can check action.context for type
    if (action.context.type === 'poll') return

    // Can manipulate the element
    el.setAttribute('data-loading', 'true')
})
```

## The Problem: Context Coupling

The current system has a **timing dependency**:

1. `addContext()` must be called BEFORE the action fires
2. Context is "pulled" and cleared when the action fires
3. If you call actions programmatically, you need to manually add context first

This creates awkward patterns like:
```javascript
// Must add context first
addContext(component, { el, directive })

// Then fire the action
component.$wire.$commit()
```

## Current Context Types

### User Context (default)
- Regular user interactions
- Gets data-loading attributes
- Highest priority

### Poll Context
```javascript
context: { type: 'poll' }
```
- Periodic refresh actions
- Doesn't get data-loading attributes
- Lower priority than user actions

### Island Context
```javascript
context: {
    island: { name: 'sidebar', mode: 'replace' }
}
```
- Targets specific islands on the page
- Contains island name and update mode

## Better Way? Proposal

### Option 1: Explicit Context Parameters
Make context explicit in all action methods:
```javascript
// Instead of disconnected addContext + action
component.$wire.$commit({ el, directive, type: 'user' })

// Or for islands
component.$wire.$refresh({ island: { name: 'sidebar' } })
```

### Option 2: Context Builder Pattern
Create a fluent API for building actions with context:
```javascript
component.$wire
    .withElement(el)
    .withDirective(directive)
    .asType('user')
    .$commit()
```

### Option 3: Unified Context in fireAction
Already partially implemented - pass all context through fireAction:
```javascript
fireAction(component, method, params, el, directive, {
    type: 'poll',
    island: { name: 'sidebar' }
})
```

## Key Insights

1. **Two separate concerns** are being mixed:
   - DOM attachment (el/directive) for interceptors
   - Server metadata (type, island, etc.)

2. **The "pull" pattern is confusing** - context is added to MessageBroker, then pulled by Action, making the flow indirect

3. **Context types aren't well defined** - it's unclear what context properties are valid/expected

4. **The timing dependency** makes programmatic calls awkward

## Recommendations

1. **Separate DOM context from server context** explicitly
2. **Make context flow more direct** - pass it through the call chain rather than storing/pulling
3. **Define context types** with TypeScript/documentation
4. **Consider removing the MessageBroker context storage** in favor of direct passing