# Livewire v4 Request System - Improvement Proposal

## Goal: Unify & Clarify Request System Entry Points

### 🎯 Core Problem

The v4 request system is currently accessed through **scattered entry points** across the codebase, making it difficult to:
1. Understand the full API surface
2. Maintain consistency
3. Control access patterns
4. Test systematically
5. Refactor safely

---

## Current State: The Mess 🔴

### Problem 1: Multiple Import Paths

Files are importing directly from deep internals:

```javascript
// Current chaos - everyone reaches into internals
import Action from '@/v4/requests/action'
import messageBroker from '@/v4/requests/messageBroker'
import interceptorRegistry from '@/v4/interceptors/interceptorRegistry'
import requestBus from '@/v4/requests/requestBus'
import MessageRequest from '@/v4/requests/messageRequest'
```

**Count of direct imports:**
- `Action`: 3 places
- `messageBroker`: 7 places
- `interceptorRegistry`: 8 places
- `requestBus`: 2 places

### Problem 2: Inconsistent Access Patterns

Different parts of the codebase interact with the request system differently:

```javascript
// Method 1: Direct Action creation
new Action(component, method, params)

// Method 2: Through messageBroker
messageBroker.addAction(action)

// Method 3: Through interceptorRegistry
interceptorRegistry.add(callback)

// Method 4: Through $wire (indirect)
component.$wire.$commit()
```

### Problem 3: Unclear Boundaries

There's no clear distinction between:
- **Public API** (what consumers should use)
- **Internal API** (what the system uses internally)
- **Feature hooks** (how features integrate)

### Problem 4: Scattered Feature Integration

Features hook into the system at different levels:

```javascript
// Some features use interceptorRegistry directly
import interceptorRegistry from '@/v4/interceptors/interceptorRegistry'

// Others use messageBroker
import messageBroker from '@/v4/requests/messageBroker'

// Some create Actions directly
import Action from '@/v4/requests/action'
```

---

## Proposed Solution: Unified Request API 🟢

### Step 1: Create Single Entry Point

Create `js/v4/requests/index.js` as the **ONLY** public interface:

```javascript
// js/v4/requests/index.js - THE ONLY EXPORT POINT

// ============================================
// PUBLIC API - For external consumption
// ============================================

export {
    // Core request creation
    createAction,           // Factory for creating actions
    fireAction,            // Fire an action through the system

    // Interception & middleware
    intercept,             // Register global interceptor
    interceptComponent,    // Register component interceptor
    interceptMethod,       // Register method-specific interceptor

    // Context management
    addContext,            // Add context for next action
    pullContext,           // Get and clear context

    // Request control
    cancelPendingRequests, // Cancel all pending
    cancelForComponent,    // Cancel for specific component

    // Status & debugging
    getPendingRequests,    // Get current pending requests
    getRequestStatus,      // Get status of specific request
    enableDebugMode,       // Turn on request logging
}

// ============================================
// FEATURE API - For Livewire features only
// ============================================

export const features = {
    // Loading states
    onLoadingStart: (callback) => { /* ... */ },
    onLoadingEnd: (callback) => { /* ... */ },

    // Error handling
    onRequestError: (callback) => { /* ... */ },
    onRequestFailure: (callback) => { /* ... */ },

    // Lifecycle hooks
    beforeRequest: (callback) => { /* ... */ },
    afterRequest: (callback) => { /* ... */ },
    beforeMorph: (callback) => { /* ... */ },
    afterMorph: (callback) => { /* ... */ },
}

// ============================================
// INTERNAL - Not exported, kept private
// ============================================

// These remain internal:
// - Message
// - MessageBroker
// - MessageRequest
// - RequestBus
// - Interceptor
```

### Step 2: Refactor All Import Statements

**BEFORE:**
```javascript
// Scattered imports from internals
import Action from '@/v4/requests/action'
import messageBroker from '@/v4/requests/messageBroker'
import interceptorRegistry from '@/v4/interceptors/interceptorRegistry'
```

**AFTER:**
```javascript
// Single, clear import
import { createAction, intercept, addContext } from '@/v4/requests'
```

### Step 3: Clear Usage Patterns

#### Pattern 1: Creating & Firing Actions

**BEFORE (confusing):**
```javascript
import Action from '@/v4/requests/action'
import messageBroker from '@/v4/requests/messageBroker'

let action = new Action(component, 'save', [data])
messageBroker.addAction(action) // or action.fire()?
```

**AFTER (clear):**
```javascript
import { fireAction } from '@/v4/requests'

fireAction(component, 'save', [data])
```

#### Pattern 2: Adding Interceptors

**BEFORE (inconsistent):**
```javascript
import interceptorRegistry from '@/v4/interceptors/interceptorRegistry'

interceptorRegistry.add(callback, component, method)
// or
component.$wire.$intercept(callback)
```

**AFTER (unified):**
```javascript
import { intercept } from '@/v4/requests'

// Global interceptor
intercept(callback)

// Component-specific
intercept(callback, { component })

// Method-specific
intercept(callback, { component, method: 'save' })
```

#### Pattern 3: Managing Context

**BEFORE (hidden):**
```javascript
import messageBroker from '@/v4/requests/messageBroker'

messageBroker.addContext(component, { type: 'user' })
let context = messageBroker.pullContext(component)
```

**AFTER (explicit):**
```javascript
import { addContext, pullContext } from '@/v4/requests'

addContext(component, { type: 'user' })
let context = pullContext(component)
```

### Step 4: Implementation Strategy

```javascript
// js/v4/requests/index.js - Full implementation

import Action from './action'
import messageBroker from './messageBroker'
import requestBus from './requestBus'
import interceptorRegistry from '../interceptors/interceptorRegistry'

// Initialize on import
requestBus.boot()

// ============================================
// PUBLIC API IMPLEMENTATIONS
// ============================================

export function createAction(component, method, params = [], options = {}) {
    const action = new Action(component, method, params, options.el, options.directive)

    if (options.context) {
        action.addContext(options.context)
    }

    return action
}

export function fireAction(component, method, params = [], options = {}) {
    const action = createAction(component, method, params, options)
    return action.fire()
}

export function intercept(callback, options = {}) {
    const { component = null, method = null } = options
    return interceptorRegistry.add(callback, component, method)
}

export function interceptComponent(component, callback) {
    return intercept(callback, { component })
}

export function interceptMethod(component, method, callback) {
    return intercept(callback, { component, method })
}

export function addContext(component, context) {
    messageBroker.addContext(component, context)
}

export function pullContext(component) {
    return messageBroker.pullContext(component)
}

export function cancelPendingRequests() {
    requestBus.requests.forEach(request => request.cancel())
}

export function cancelForComponent(component) {
    requestBus.requests.forEach(request => {
        if (request.hasMessageFor?.(component)) {
            request.cancel()
        }
    })
}

export function getPendingRequests() {
    return Array.from(requestBus.requests)
}

export function getRequestStatus(component) {
    const message = messageBroker.getMessage(component)
    return message?.status || 'idle'
}

export function enableDebugMode() {
    window.__livewire_request_debug = true
    console.log('🔍 Livewire Request Debug Mode Enabled')
}

// ============================================
// FEATURE HOOKS
// ============================================

export const features = {
    onLoadingStart(callback) {
        return intercept(({ request }) => {
            request.beforeSend(() => callback())
        })
    },

    onLoadingEnd(callback) {
        return intercept(({ request }) => {
            request.afterRender(() => callback())
        })
    },

    onRequestError(callback) {
        return intercept(({ request }) => {
            request.onError(callback)
        })
    },

    onRequestFailure(callback) {
        return intercept(({ request }) => {
            request.onFailure(callback)
        })
    },

    beforeRequest(callback) {
        return intercept(({ request }) => {
            request.beforeSend(callback)
        })
    },

    afterRequest(callback) {
        return intercept(({ request }) => {
            request.afterResponse(callback)
        })
    },

    beforeMorph(callback) {
        return intercept(({ request }) => {
            request.beforeMorph(callback)
        })
    },

    afterMorph(callback) {
        return intercept(({ request }) => {
            request.afterMorph(callback)
        })
    },
}

// ============================================
// DEBUGGING HELPERS (only in dev mode)
// ============================================

if (process.env.NODE_ENV !== 'production') {
    window.__livewire_requests = {
        getPending: getPendingRequests,
        getStatus: getRequestStatus,
        cancelAll: cancelPendingRequests,
        enableDebug: enableDebugMode,
    }
}
```

---

## Migration Examples

### Example 1: wire:model Directive

**BEFORE:**
```javascript
// js/directives/wire-model.js
import Action from '@/v4/requests/action'

// Unclear how to properly fire an action
let update = () => {
    if (window.livewireV4) {
        component.addActionContext({ el, directive })
    }
    component.$wire.$commit()
}
```

**AFTER:**
```javascript
// js/directives/wire-model.js
import { fireAction, addContext } from '@/v4/requests'

let update = () => {
    addContext(component, { el, directive })
    fireAction(component, '$commit')
}
```

### Example 2: Feature Integration

**BEFORE:**
```javascript
// js/v4/features/supportDataLoading.js
import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'

interceptorRegistry.add(({ action, component, request, el, directive }) => {
    // Complex setup...
})
```

**AFTER:**
```javascript
// js/v4/features/supportDataLoading.js
import { features } from '@/v4/requests'

features.onLoadingStart(() => {
    // Show loading state
})

features.onLoadingEnd(() => {
    // Hide loading state
})
```

### Example 3: $wire Implementation

**BEFORE:**
```javascript
// js/$wire.js - Multiple imports from internals
import messageBroker from './v4/requests/messageBroker'
import interceptorRegistry from './v4/interceptors/interceptorRegistry'
import Action from './v4/requests/action'

wireProperty('$set', (component) => async (property, value, live = true) => {
    if (live) {
        component.queueUpdate(property, value)
        let action = new Action(component, '$set')
        return action.fire()
    }
})

wireProperty('$intercept', (component) => (method, callback = null) => {
    return interceptorRegistry.add(callback, component, method)
})
```

**AFTER:**
```javascript
// js/$wire.js - Single, clear import
import { fireAction, interceptMethod } from '@/v4/requests'

wireProperty('$set', (component) => async (property, value, live = true) => {
    if (live) {
        component.queueUpdate(property, value)
        return fireAction(component, '$set')
    }
})

wireProperty('$intercept', (component) => (method, callback = null) => {
    if (callback === null) {
        callback = method
        method = null
    }
    return method
        ? interceptMethod(component, method, callback)
        : interceptComponent(component, callback)
})
```

---

## Benefits of This Approach

### 1. **Single Source of Truth**
- All request system interaction goes through one file
- Easy to see the entire API at a glance
- Clear documentation point

### 2. **Encapsulation**
- Internal implementation details are hidden
- Can refactor internals without breaking consumers
- Prevents reaching into implementation details

### 3. **Type Safety** (if using TypeScript in future)
```typescript
// Easy to add types to the single export point
export function fireAction(
    component: Component,
    method: string,
    params?: any[],
    options?: ActionOptions
): Promise<any>
```

### 4. **Testing**
```javascript
// Easy to mock the entire request system
jest.mock('@/v4/requests', () => ({
    fireAction: jest.fn(),
    intercept: jest.fn(),
    // etc...
}))
```

### 5. **Debugging**
```javascript
// All requests flow through one place
export function fireAction(...args) {
    if (window.__livewire_request_debug) {
        console.log('🚀 Firing action:', ...args)
    }
    // ... rest of implementation
}
```

### 6. **Progressive Enhancement**
```javascript
// Easy to add new features without breaking existing code
export function fireActionWithRetry(component, method, params, retries = 3) {
    // New functionality, old API still works
}
```

---

## Implementation Plan

### Phase 1: Create Unified API (Non-Breaking)
1. Create new `js/v4/requests/index.js` with all exports
2. Keep existing files working (backwards compatibility)
3. Add deprecation warnings in dev mode

### Phase 2: Migration (Gradual)
1. Update core files (`$wire.js`, `component.js`)
2. Update directives (`wire-model.js`, `wire-poll.js`)
3. Update features one by one
4. Update tests

---

## Measuring Success

### Before Metrics:
- **Entry points**: 20+ different import statements
- **API surface**: Unclear, spread across multiple files
- **Learning curve**: High - need to understand internals
- **Refactor risk**: High - changing internals breaks consumers

### After Metrics:
- **Entry points**: 1 (`@/v4/requests`)
- **API surface**: ~15 clear, documented functions
- **Learning curve**: Low - single file to understand
- **Refactor risk**: Low - stable public API

---

## Next Steps

1. **Review & Approve** this proposal
2. **Create the unified API file** with backwards compatibility
3. **Test with one component** (e.g., wire:model)
4. **Gradually migrate** other components
5. **Document the new API**
6. **Deprecate old patterns**

---

## Questions to Consider after initial refactor

1. Should we namespace by functionality?
   ```javascript
   import { actions, interceptors, context } from '@/v4/requests'
   actions.fire(...)
   interceptors.add(...)
   context.add(...)
   ```

2. Should we provide a fluent API?
   ```javascript
   import { request } from '@/v4/requests'
   request()
     .for(component)
     .withContext({ type: 'user' })
     .fire('save', [data])
   ```

3. Should we expose request creation for advanced use cases?
   ```javascript
   import { createRequest } from '@/v4/requests'
   const request = createRequest()
   request.addMessage(...)
   request.send()
   ```

---

This is the smallest, most impactful improvement we can make to the v4 request system. It doesn't change how the system works internally, but it dramatically improves how the outside world interacts with it.