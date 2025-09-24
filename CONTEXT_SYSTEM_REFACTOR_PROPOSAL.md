# Context System Refactor - Clean API Spec

## Core Concepts

Two distinct types of data flow through the request system:

1. **Origin** - Local data for interceptors (el, directive) - NOT sent to server
2. **Metadata** - Server data (type, island info) - sent with request payload

## File Structure

```
js/request/
├── index.js           # Public API exports
├── action.js          # Action class
├── actionOrigin.js    # Origin management
├── messageBroker.js
└── ...
```

## API Design

### actionOrigin.js - Simple Global Origin Store
```javascript
// js/request/actionOrigin.js
let nextActionOrigin = null

export function setNextActionOrigin(origin) {
    nextActionOrigin = origin
}

export function pullNextActionOrigin() {
    let origin = nextActionOrigin
    nextActionOrigin = null  // Self-clearing
    return origin || {}
}
```

### action.js - Simple Data Class
```javascript
// js/request/action.js
import { pullNextActionOrigin } from './actionOrigin.js'

export default class Action {
    constructor(component, method, params = [], metadata = {}) {
        this.component = component
        this.method = method
        this.params = params
        this.metadata = metadata

        // Automatically pull any pending origin
        this.origin = pullNextActionOrigin()
    }
}
```

### request/index.js - Clean Public API
```javascript
// js/request/index.js
import Action from './action.js'
import interceptorRegistry from './interceptors/interceptorRegistry.js'
import messageBroker from './messageBroker.js'

export { setNextActionOrigin } from './actionOrigin.js'
export { intercept } from './interceptors/interceptorRegistry.js'

// Core action firing
export function fireAction(component, method, params = [], metadata = {}) {
    let action = new Action(component, method, params, metadata)

    // Fire interceptors with full context
    interceptorRegistry.fire(action)

    // Add to message broker and return promise
    return messageBroker.addAction(action)
}
```

## Usage Patterns

### Directives with Origin
```javascript
// wire-model.js
import { setNextActionOrigin } from '@/request'

setNextActionOrigin({ el, directive })
component.$wire.$commit()
```

### Actions with Both Origin and Metadata
```javascript
// wire-poll.js - with both origin and metadata
setNextActionOrigin({ el, directive })
fireAction(component, '$refresh', [], { type: 'poll' })
```

### Programmatic Actions with Just Metadata
```javascript
// $wire.js - Island refresh (no origin needed)
fireAction(component, '$refresh', [], {
    type: 'refresh',
    island: { name: 'sidebar', mode: 'replace' }
})
```

### Complex Actions (wire-wildcard)
```javascript
// wire-wildcard.js
setNextActionOrigin({ el, directive })
evaluateActionExpression(component, el, expression)
// Alpine calls $wire.someMethod() which eventually calls fireAction
```

## Interceptor API

Clean and simple - no special parameters:

```javascript
intercept(({ action, component, request }) => {
    // Origin data (local only)
    if (!action.origin.el) return
    let { el, directive } = action.origin

    // Metadata (sent to server)
    if (action.metadata.type === 'poll') return

    // Do interceptor work
    el.setAttribute('data-loading', 'true')

    request.afterResponse(() => {
        el.removeAttribute('data-loading')
    })
})
```

## $wire Method Updates

All $wire methods use fireAction with appropriate metadata:

```javascript
// $wire.js
wireProperty('$refresh', (component) => () => {
    return fireAction(component, '$refresh')
})

wireProperty('$commit', (component) => () => {
    return fireAction(component, '$commit')
})

wireProperty('$set', (component) => (property, value, live = true) => {
    dataSet(component.reactive, property, value)

    if (live) {
        component.queueUpdate(property, value)
        return fireAction(component, '$set')
    }

    return Promise.resolve()
})

wireProperty('$island', (component) => (name, mode = null) => {
    return fireAction(component, '$refresh', [], {
        island: { name, mode }
    })
})
```

## Message Context Removal

Remove the confusing MessageBroker context system entirely:

```javascript
// REMOVE these from MessageBroker:
// - addContext()
// - pullContext()

// REMOVE these from component:
// - addActionContext()

// REMOVE these from request/index.js:
// - addContext()
// - pullContext()
```

## Implementation Plan

### Phase 1: Core Infrastructure
1. Create `actionOrigin.js` module
2. Update Action constructor to auto-pull origin
3. Move firing logic from Action.fire() to fireAction
4. Update fireAction to handle interceptors and message broker
5. Clean up request/index.js exports

### Phase 2: Directive Updates
1. Replace all `addContext()` calls with `setNextActionOrigin()`
2. Update all $wire methods to use fireAction
3. Remove MessageBroker context methods

### Phase 3: Interceptor Updates
1. Update all interceptors to use `action.origin` and `action.metadata`
2. Remove el/directive parameters from intercept callback

### Phase 4: Cleanup
1. Remove old context system completely
2. Remove backwards compatibility shims
3. Update documentation

## Benefits

1. **Crystal clear separation**: Origin vs Metadata
2. **Dead simple API**: `setNextActionOrigin()` explains exactly what it does
3. **No component coupling**: Global origin store works for all cases
4. **Automatic behavior**: Action constructor handles origin pulling
5. **Clean interceptors**: Simple `action.origin` and `action.metadata` access
6. **Consistent patterns**: All actions go through fireAction
7. **Simple Action class**: Just a data container, no business logic

## Example Migration

### Before (confusing):
```javascript
// Multiple concepts mixed together
addContext(component, {
    type: 'user',  // Server metadata
    el,            // Local interceptor data
    directive      // Local interceptor data
})
component.$wire.$commit()

// Interceptor gets mixed bag
intercept(({ action, el, directive }) => {
    // el/directive are special, filtered from server
    // action.context has server data
})
```

### After (clean):
```javascript
// Clear separation
setNextActionOrigin({ el, directive })  // Local interceptor data
fireAction(component, '$commit', [], { type: 'user' })  // Server metadata

// Interceptor gets clean separation
intercept(({ action }) => {
    let { el, directive } = action.origin    // Local data
    let { type } = action.metadata           // Server data
})
```

## Summary

This creates a much cleaner architecture where:
- **Origin** is always local interceptor data, never sent to server
- **Metadata** is always server data, sent with payload
- **setNextActionOrigin** has perfect naming that explains its purpose
- **No backwards compatibility baggage** - clean slate design
- **Simple global state** works perfectly for synchronous JavaScript