# Livewire v4 Request System - Visual Guide 🚀

## Table of Contents
1. [Overview](#overview)
2. [Core Components](#core-components)
3. [The Request Flow - Step by Step](#the-request-flow---step-by-step)
4. [Component Relationships](#component-relationships)
5. [Detailed Flow Diagrams](#detailed-flow-diagrams)
6. [Key Concepts Explained](#key-concepts-explained)

---

## Overview

The Livewire v4 request system is a sophisticated architecture that manages communication between the frontend and backend. Think of it as a postal service where:
- **Actions** are letters you want to send
- **Messages** are envelopes that hold multiple letters going to the same address
- **MessageBroker** is the local post office that collects and sorts mail
- **Requests** are mail trucks that deliver batches of envelopes
- **RequestBus** is the dispatch center managing all trucks
- **Interceptors** are inspectors that can examine/modify mail at various checkpoints

---

## Core Components

### 🎯 Action (`action.js`)
**What it is:** A single user interaction (clicking a button, typing in a field)
**Think of it as:** A letter you want to send

```
┌─────────────────┐
│     ACTION      │
├─────────────────┤
│ • component     │ ← Which Livewire component
│ • method        │ ← What method to call
│ • params        │ ← Arguments to pass
│ • el            │ ← DOM element that triggered it
│ • directive     │ ← wire:directive info
│ • context       │ ← Additional metadata
└─────────────────┘
```

### 📨 Message (`message.js`)
**What it is:** Container for one or more actions from the same component
**Think of it as:** An envelope containing multiple letters to the same address

```
┌──────────────────────┐
│      MESSAGE         │
├──────────────────────┤
│ • component          │ ← The Livewire component
│ • actions []         │ ← Array of Actions
│ • updates {}         │ ← Component state changes
│ • payload {}         │ ← Data to send to server
│ • interceptors Set() │ ← Lifecycle hooks
│ • status             │ ← waiting/buffering/preparing/succeeded/failed
└──────────────────────┘
```

### 📬 MessageBroker (`messageBroker.js`)
**What it is:** Manages message creation and batching
**Think of it as:** The local post office

```
┌────────────────────────┐
│    MESSAGE BROKER      │
├────────────────────────┤
│ • messages Map()       │ ← componentId → Message
│ • getMessage()         │ ← Get or create message
│ • addAction()         │ ← Add action to message
│ • bufferForFiveMs()   │ ← Wait 5ms to batch
│ • prepareRequests()   │ ← Bundle messages into requests
└────────────────────────┘
```

### 🚚 MessageRequest (`messageRequest.js`)
**What it is:** HTTP request containing multiple messages
**Think of it as:** A delivery truck

```
┌────────────────────────┐
│    MESSAGE REQUEST     │
├────────────────────────┤
│ • messages Set()       │ ← Multiple messages
│ • send()              │ ← Makes actual HTTP call
│ • cancel()            │ ← Abort the request
│ • succeed()           │ ← Handle success response
│ • fail()              │ ← Handle error response
└────────────────────────┘
```

### 🚦 RequestBus (`requestBus.js`)
**What it is:** Manages all active requests
**Think of it as:** The dispatch center

```
┌────────────────────────┐
│     REQUEST BUS        │
├────────────────────────┤
│ • requests Set()       │ ← Active requests
│ • add()               │ ← Add new request
│ • remove()            │ ← Remove completed request
│ • cancelConflicts()   │ ← Cancel conflicting requests
└────────────────────────┘
```

### 🔍 Interceptor (`interceptor.js`)
**What it is:** Lifecycle hooks for the request process
**Think of it as:** Quality control checkpoints

```
┌────────────────────────┐
│     INTERCEPTOR        │
├────────────────────────┤
│ Lifecycle Hooks:       │
│ • beforeSend()        │
│ • afterSend()         │
│ • beforeResponse()    │
│ • afterResponse()     │
│ • beforeRender()      │
│ • afterRender()       │
│ • beforeMorph()       │
│ • afterMorph()        │
│ • onError()           │
│ • onFailure()         │
│ • onSuccess()         │
│ • onCancel()          │
└────────────────────────┘
```

---

## The Request Flow - Step by Step

### 🎬 The Journey of a Click

```
USER CLICKS BUTTON
       ↓
1. ACTION CREATED
       ↓
2. INTERCEPTORS FIRE
       ↓
3. MESSAGE BROKER RECEIVES ACTION
       ↓
4. ACTION ADDED TO MESSAGE
       ↓
5. BUFFERED FOR 5ms (batching)
       ↓
6. MESSAGES PREPARED
       ↓
7. MESSAGES GROUPED INTO REQUESTS
       ↓
8. REQUEST SENT VIA REQUEST BUS
       ↓
9. HTTP REQUEST TO SERVER
       ↓
10. RESPONSE RECEIVED
       ↓
11. MESSAGES SUCCEED/FAIL
       ↓
12. DOM UPDATES (MORPH)
       ↓
13. INTERCEPTORS CLEANUP
```

---

## Component Relationships

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         USER INTERACTION                     │
└────────────────────────────┬────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                          ACTION                              │
│  new Action(component, method, params, el, directive)       │
└────────────────────────────┬────────────────────────────────┘
                             ↓ action.fire()
┌─────────────────────────────────────────────────────────────┐
│                    INTERCEPTOR REGISTRY                      │
│  interceptorRegistry.fire(action)                           │
│  Creates Interceptor instances & adds to MessageBroker      │
└────────────────────────────┬────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                      MESSAGE BROKER                          │
│  • Gets/creates Message for component                       │
│  • Adds Action to Message                                   │
│  • Buffers for 5ms (batching)                              │
└────────────────────────────┬────────────────────────────────┘
                             ↓ After 5ms
┌─────────────────────────────────────────────────────────────┐
│                    MESSAGE PREPARATION                       │
│  • Collects component updates                              │
│  • Creates payload (snapshot + updates + calls)            │
│  • Groups Messages into MessageRequests                    │
└────────────────────────────┬────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                       REQUEST BUS                            │
│  • Checks for conflicts/cancellations                      │
│  • Manages active requests                                 │
│  • Triggers request.send()                                 │
└────────────────────────────┬────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                     HTTP NETWORK CALL                        │
│  fetch('/livewire/update', { method: 'POST', body: ... })  │
└────────────────────────────┬────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                    RESPONSE PROCESSING                       │
│  • Parse response                                          │
│  • Match responses to Messages                             │
│  • Call message.succeed() or message.fail()               │
└────────────────────────────┬────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                      DOM MORPHING                            │
│  • Apply component updates                                 │
│  • Morph HTML changes                                      │
│  • Process effects                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## Detailed Flow Diagrams

### 📊 Action → Message Flow

```
   Action.fire()
        │
        ├──→ Pull context from MessageBroker
        │    (el, directive, other metadata)
        │
        ├──→ InterceptorRegistry.fire(action)
        │    │
        │    ├──→ Creates Interceptor instances
        │    │
        │    └──→ Adds to MessageBroker
        │
        └──→ MessageBroker.addAction(action)
             │
             ├──→ Get/Create Message for component
             │
             ├──→ message.addAction(action, resolver)
             │
             └──→ bufferMessageForFiveMs(message)
                  │
                  └──→ setTimeout(5ms) → prepareRequests()
```

### 🔄 Message Batching Process

```
TIME: 0ms    - User clicks button A
              └─→ Action A → Message 1 (buffering)

TIME: 2ms    - User types in input B
              └─→ Action B → Message 1 (still buffering)

TIME: 4ms    - Another component triggers action C
              └─→ Action C → Message 2 (buffering)

TIME: 5ms    - Buffer timeout fires!
              └─→ prepareRequests()
                   │
                   ├─→ Message 1 (Actions A + B)
                   ├─→ Message 2 (Action C)
                   │
                   └─→ MessageRequest containing both Messages
                        │
                        └─→ Single HTTP request with all data
```

### 🎭 Interceptor Lifecycle

```
ACTION FIRED
     │
     ├──→ beforeSend()      [Before HTTP request]
     │         ↓
     ├──→ afterSend()       [After HTTP request sent]
     │         ↓
     ├──→ beforeResponse()  [Response received, before processing]
     │         ↓
     ├──→ afterResponse()   [After response processed]
     │         ↓
     ├──→ beforeRender()    [Before DOM updates]
     │         ↓
     ├──→ beforeMorph()     [Before morphing HTML]
     │         ↓
     ├──→ afterMorph()      [After morphing HTML]
     │         ↓
     ├──→ afterRender()     [After all DOM updates]
     │         ↓
     └──→ returned()        [Cleanup phase]

ON ERROR:
     ├──→ onError()         [Network/fetch errors]
     ├──→ onFailure()      [HTTP error responses]
     ├──→ onCancel()       [Request cancelled]
     └──→ onSuccess()      [Everything worked!]
```

---

## Key Concepts Explained

### 🕐 The 5ms Buffer

**Why 5ms?** This micro-delay allows multiple rapid-fire actions to be batched together into a single HTTP request.

```
Without Buffering:              With 5ms Buffer:
─────────────────              ─────────────────
Click → HTTP Request 1         Click ─┐
Type  → HTTP Request 2         Type  ├→ Wait 5ms → 1 HTTP Request
Click → HTTP Request 3         Click ─┘

3 requests = slower            1 request = faster!
```

### 🚫 Cancellation Logic

Messages can cancel each other based on priority:

```
Priority Order:
1. User actions (highest)
2. Refresh actions
3. Poll actions (lowest)

Example scenarios:
─────────────────
• Poll running + User clicks → Poll cancelled
• User action running + Poll fires → Poll waits
• Old user action + New user action → Old cancelled
```

### 🏝️ Islands vs Components

The system distinguishes between regular components and "islands" (isolated components):

```
Regular Component:           Island:
─────────────────           ─────────────────
│ Full page context         │ Isolated context
│ Can affect other          │ Self-contained
│ components                │ Updates independently
│ Standard morphing         │ Special rendering
```

### 📦 Payload Structure

What actually gets sent to the server:

```json
{
  "_token": "csrf-token-here",
  "components": [
    {
      "snapshot": {
        "memo": { "id": "component-id", "name": "ComponentName" },
        "data": { /* component state */ }
      },
      "updates": {
        "user.name": "New Value",
        "counter": 42
      },
      "calls": [
        {
          "method": "save",
          "params": ["arg1", "arg2"],
          "context": { /* metadata */ }
        }
      ]
    }
  ]
}
```

### 🔄 Message Status Flow

```
    waiting
       ↓
   buffering (5ms delay)
       ↓
   preparing (building payload)
       ↓
   [HTTP Request]
       ↓
  ┌────┴────┬──────┬────────┐
  ↓         ↓      ↓        ↓
succeeded  failed  errored  cancelled
```

---

## Common Scenarios

### Scenario 1: Simple Button Click

```
1. User clicks "Save" button
2. Action created: new Action(component, 'save', [], button, 'click')
3. Action fires → Interceptors notified
4. MessageBroker creates/gets Message for component
5. Action added to Message
6. 5ms buffer starts
7. After 5ms: Message prepared with component state
8. MessageRequest created with Message
9. RequestBus sends HTTP request
10. Server responds with updated snapshot
11. Message succeeds → DOM morphs with new HTML
12. Interceptors clean up
```

### Scenario 2: Rapid Form Input

```
1. User types "H" in search box
2. Action created for 'search' with param "H"
3. Buffered for 5ms
4. User types "e" (now "He")
5. New Action for 'search' with param "He"
6. Both actions in same Message (same component)
7. After 5ms: Single request with both actions
8. Server processes in sequence
9. Returns final state after both actions
10. DOM updates once with final result
```

### Scenario 3: Polling Conflict

```
1. Poll timer fires → Action for 'refresh'
2. Message created and buffered
3. User clicks button → Action for 'save'
4. Cancellation check: User action > Poll action
5. Poll Message cancelled
6. User action proceeds alone
7. After user action completes, polling resumes
```

---

## Tips for Understanding the Flow

1. **Follow the Action**: Start with `Action.fire()` and trace through each step
2. **Watch the Timing**: The 5ms buffer is key to batching
3. **Understand Priorities**: User > Refresh > Poll
4. **Track the Status**: Messages move through distinct status phases
5. **Interceptors are Everywhere**: They hook into every major step

## Debugging Helpers

To see the flow in action, add console.logs:

```javascript
// In action.js
fire() {
    console.log('🎯 Action fired:', this.method, this.params)
    // ...
}

// In messageBroker.js
bufferMessageForFiveMs(message) {
    console.log('⏱️ Buffering message for 5ms')
    // ...
}

// In messageRequest.js
async send() {
    console.log('🚀 Sending request with', this.messages.size, 'messages')
    // ...
}
```

---

## Summary

The v4 request system is like a well-orchestrated postal service:

1. **Actions** are individual letters
2. **Messages** are envelopes collecting letters to the same address
3. **MessageBroker** is the post office sorting mail
4. **Requests** are delivery trucks carrying multiple envelopes
5. **RequestBus** is dispatch managing all trucks
6. **Interceptors** are quality checkpoints along the way

The genius is in the batching (5ms buffer) and intelligent cancellation that prevents unnecessary network traffic while maintaining responsiveness.

---

## Entry Points and Usage Throughout the Codebase

### 🚪 Main Entry Points

#### 1. **$wire Object** (`js/$wire.js`)
The primary way components interact with the request system:

```javascript
// Entry points via $wire:
$wire.$set(property, value)      // Creates Action → fires through system
$wire.$call(method, ...params)   // Creates Action → fires through system
$wire.$commit()                  // Creates Action for pending updates
$wire.$intercept(callback)       // Registers interceptor
```

**Files that import/use:**
- `js/index.js` - Exports to global Livewire object
- `js/directives/wire-model.js` - Uses for model binding
- `js/component.js` - Creates $wire object for each component

#### 2. **wire:model Directive** (`js/directives/wire-model.js`)
```javascript
// When input changes:
component.addActionContext({ el, directive })
component.$wire.$commit() // → Creates Action → MessageBroker
```

#### 3. **wire:poll Directive** (`js/directives/wire-poll.js`)
```javascript
import Action from '@/v4/requests/action'
// Creates polling Actions with type: 'poll'
new Action(component, method, params)
```

#### 4. **wire:click and Other Event Directives**
Via `$wire.$call()` which creates Actions internally

### 📍 Where Each Component is Used

#### **Action** Usage:
```
Files that import Action:
├── js/$wire.js:14
│   └── new Action(component, '$set')
├── js/directives/wire-model.js:6
│   └── Indirectly via $commit()
└── js/directives/wire-poll.js:3
    └── new Action(component, method, params)
```

#### **MessageBroker** Usage:
```
Files that use MessageBroker:
├── js/v4/requests/action.js
│   ├── messageBroker.pullContext()
│   └── messageBroker.addAction()
├── js/v4/interceptors/interceptorRegistry.js
│   └── MessageBroker.addInterceptor()
├── js/component.js:5
│   └── messageBroker.addContext()
└── js/$wire.js:9
    └── Import only
```

#### **InterceptorRegistry** Usage:
```
Files that register interceptors:
├── js/$wire.js:12,186
│   └── interceptorRegistry.add(callback, component, method)
├── js/v4/requests/action.js:1,37
│   └── interceptorRegistry.fire(action)
├── js/v4/features/supportDataLoading.js
│   └── interceptorRegistry.add() for loading states
├── js/v4/features/supportPreserveScroll.js
│   └── interceptorRegistry.add() for scroll preservation
├── js/v4/features/supportWireIsland.js
│   └── interceptorRegistry.add() for island support
├── js/features/supportIslands.js
│   └── interceptorRegistry.add() for island rendering
└── js/index.js:7,13
    └── Exports intercept() method to global API
```

#### **RequestBus** Usage:
```
Files that use RequestBus:
├── js/v4/requests/index.js:3
│   └── requestBus.boot() - Initializes system
├── js/v4/requests/messageBroker.js
│   └── Uses Je.add(request) to dispatch requests
└── js/v4/requests/request.js
    └── Je.remove(this) when request completes
```

### 🔌 Feature Integrations

#### **File Uploads** (`js/features/supportFileUploads.js`)
- Hooks into the request system for upload progress
- Uses interceptors for upload lifecycle

#### **Loading States** (`js/v4/features/supportDataLoading.js`)
```javascript
interceptorRegistry.add(({ action, request }) => {
    // Manages wire:loading states
    request.beforeSend(() => showLoadingStates())
    request.afterRender(() => hideLoadingStates())
})
```

#### **Scroll Preservation** (`js/v4/features/supportPreserveScroll.js`)
```javascript
interceptorRegistry.add(({ request }) => {
    request.beforeMorph(() => saveScrollPosition())
    request.afterMorph(() => restoreScrollPosition())
})
```

#### **Islands Support** (`js/v4/features/supportWireIsland.js`)
```javascript
interceptorRegistry.add(({ action }) => {
    // Adds island context to actions
    action.addContext({ island: { name, mode } })
})
```

#### **Error Handling** (`js/v4/features/supportErrors.js`)
- Provides `$wire.$errors` object
- Reads from message responses

#### **Pagination** (`js/v4/features/supportPaginators.js`)
- Provides `$wire.$paginator` object
- Manages paginated data state

### 🎯 Global API Entry Points

From `js/index.js`:
```javascript
window.Livewire = {
    // Direct request system access:
    intercept: (callback) => interceptorRegistry.add(callback),

    // Component access (which use request system):
    find: (id) => findComponent(id),  // → component.$wire
    all: () => allComponents(),        // → components with $wire

    // Hooks that trigger during request lifecycle:
    hook: (name, callback) => on(name, callback)
}
```

### 📊 Request Flow Triggers

Common user interactions that trigger the request system:

1. **Form Input** → `wire:model` → `$commit()` → Action → MessageBroker
2. **Button Click** → `wire:click` → `$call()` → Action → MessageBroker
3. **Polling** → `wire:poll` → Action (type: 'poll') → MessageBroker
4. **Property Update** → `$wire.$set()` → Action → MessageBroker
5. **Manual Call** → `$wire.methodName()` → `$call()` → Action → MessageBroker
6. **Refresh** → `$wire.$refresh()` → Action → MessageBroker

### 🔍 Debugging Entry Points

To trace a request from start to finish, add logs at these key points:

```javascript
// 1. Action creation (js/$wire.js or directives)
console.log('Creating action:', method, params)

// 2. Action firing (js/v4/requests/action.js:fire())
console.log('Action firing:', this.method)

// 3. Message buffering (js/v4/requests/messageBroker.js:bufferMessageForFiveMs())
console.log('Buffering message for component:', message.component.id)

// 4. Request preparation (js/v4/requests/messageBroker.js:prepareRequests())
console.log('Preparing', messages.size, 'messages')

// 5. HTTP send (js/v4/requests/messageRequest.js:send())
console.log('Sending HTTP request with', this.messages.size, 'messages')

// 6. Response handling (js/v4/requests/message.js:succeed())
console.log('Message succeeded for component:', this.component.id)
```

### 📦 Build Output

The v4 request system is bundled into:
- `dist/livewire.js` - Standard build
- `dist/livewire.esm.js` - ES Module build
- `dist/livewire.csp.js` - Content Security Policy compliant
- `dist/livewire.min.js` - Minified production build

All contain the complete v4 request system initialized via `requestBus.boot()`.

---

🎉 Now you understand the Livewire v4 request system AND where to find it in action! 🎉