# Livewire Request System Architecture

## Overview

Livewire has two distinct request systems:
- **V3 System**: The current stable request system using Commits and Pools
- **V4 System**: The new request system using Actions, Messages, and Interceptors

Both systems coexist in the codebase, with V4 being conditionally enabled via `window.livewireV4` flag.

## V3 Request System

### Core Components

#### 1. CommitBus (`js/request/bus.js`)
- Manages pooling of multiple commits and sending them to the server
- Buffers commits for 5ms to batch rapid-fire events
- Creates pools of commits to send as network requests

#### 2. Commit (`js/request/commit.js`)
- Represents an individual component updating itself server-side
- Contains component state changes and method calls
- Generates payload with snapshot diff and queued updates

#### 3. RequestPool (`js/request/pool.js`)
- Contains a list of commits to be sent together
- Determines if commits should be bundled or isolated
- Sends the actual HTTP request via `sendRequest()`

### Request Flow (V3)

```
User Action → $wire.method() → requestCall() or requestCommit()
    ↓
CommitBus.add(component)
    ↓
[5ms buffer]
    ↓
CommitBus.corraleCommitsIntoPools()
    ↓
RequestPool.send()
    ↓
sendRequest() [HTTP POST]
    ↓
Response handling → Component update
```

### Key V3 Functions

- `requestCommit(component)`: Creates a commit for syncing component state
- `requestCall(component, method, params)`: Creates a commit with a method call
- `sendRequest(pool)`: Sends the HTTP request with pooled commits

## V4 Request System

### Core Components

#### 1. Action (`js/v4/requests/action.js`)
- Represents a user action (method call, $set, $refresh, etc.)
- Can have context (e.g., island mode, polling, etc.)
- Triggers interceptors when fired

#### 2. Message (`js/v4/requests/message.js`)
- Container for one or more actions from the same component
- Manages interceptors and lifecycle callbacks
- Handles response processing and DOM morphing
- Can be cancelled based on priority (user > refresh > poll)

#### 3. MessageBroker (`js/v4/requests/messageBroker.js`)
- Central hub for managing messages
- Buffers messages for 5ms (like V3)
- Corrales messages into requests
- Manages context passing between actions

#### 4. MessageRequest (`js/v4/requests/messageRequest.js`)
- HTTP request containing one or more messages
- Extends base Request class
- Handles fetch operations and response distribution

#### 5. RequestBus (`js/v4/requests/requestBus.js`)
- Manages active requests
- Handles request cancellation logic
- Prevents conflicting requests

#### 6. Interceptors (`js/v4/interceptors/`)
- Powerful middleware system for request lifecycle
- Can be global or component-specific
- Provides hooks for every stage of the request

### Request Flow (V4)

```
User Action → $wire.method() → new Action(component, method, params)
    ↓
Action.fire()
    ↓
InterceptorRegistry.fire(action) [Interceptors attached]
    ↓
MessageBroker.addAction(action)
    ↓
[5ms buffer]
    ↓
MessageBroker.prepareRequests()
    ↓
MessageBroker.corraleMessagesIntoRequests()
    ↓
RequestBus.add(request)
    ↓
MessageRequest.send() [HTTP POST]
    ↓
Response → Message.succeed() → Interceptor callbacks → DOM morph
```

### Interceptor Lifecycle

Interceptors can hook into these stages:
1. `beforeSend`: Before request is sent
2. `afterSend`: After request is sent
3. `beforeResponse`: Before processing response
4. `afterResponse`: After processing response
5. `beforeRender`: Before DOM updates
6. `beforeMorph`: Before morphing specific elements
7. `afterMorph`: After morphing
8. `afterRender`: After all DOM updates
9. `onSuccess`, `onError`, `onFailure`, `onCancel`: Status handlers
10. `returned`: Final cleanup

### Cancellation Logic (V4)

V4 introduces smart request cancellation:
- Polls cancel newer polls (prevents polling loops)
- User actions cancel existing polls
- Island-specific actions don't interfere with component actions
- Same-component requests cancel older requests (with exceptions)

## $wire Object Integration

The `$wire` object (`js/$wire.js`) is the primary API for both systems:

### Key Methods
- `$wire.method()`: Calls a server method
- `$wire.$set()`: Updates a property
- `$wire.$refresh()`: Refreshes component
- `$wire.$commit()`: Syncs state with server
- `$wire.$intercept()`: Adds interceptor (V4 only)

### System Detection
```javascript
if (window.livewireV4) {
    // Use V4 system - Action
    let action = new Action(component, method, params)
    return action.fire()
} else {
    // Use V3 system - Commit
    return await requestCall(component, method, params)
}
```

## Key Differences

### V3 System
- **Architecture**: Commit → Pool → Request
- **Batching**: Commits pooled by isolation rules
- **Lifecycle**: Basic success/fail callbacks
- **Simplicity**: Straightforward request/response

### V4 System
- **Architecture**: Action → Message → Request
- **Batching**: Messages grouped, actions can have context
- **Lifecycle**: Rich interceptor system
- **Features**: 
  - Request cancellation
  - Priority handling (user > refresh > poll)
  - Island support
  - Fine-grained lifecycle hooks
  - Context passing between actions

## Component Integration

### Component Class (`js/component.js`)
- Manages component state (canonical, ephemeral, reactive)
- Queues updates for next request
- Processes effects from server responses
- Integrates with both V3 and V4 systems

### State Management
- **Canonical**: Last known server state
- **Ephemeral**: Current client state
- **Reactive**: Alpine.js reactive wrapper around ephemeral
- **QueuedUpdates**: Updates to be sent on next request

## Network Layer

Both systems share similar network characteristics:
- POST requests to Livewire update endpoint
- JSON payload with components array
- CSRF token included
- Response contains snapshots and effects
- Error handling for 419 (expired), 503 (offline), etc.

## Migration Path

The V4 system is designed to be backwards compatible:
1. Both systems coexist in the codebase
2. V4 enabled via `window.livewireV4` flag
3. `$wire` API remains consistent
4. Components work with either system

## Benefits of V4

1. **Better Request Management**: Smart cancellation prevents race conditions
2. **Interceptors**: Powerful middleware for extending behavior
3. **Context Awareness**: Actions can carry context through the request
4. **Island Support**: Better support for partial updates
5. **Developer Experience**: More hooks for debugging and extending

## Implementation Notes

- V4 is currently experimental (console.log shows "v4 requests enabled")
- Both systems handle the 5ms buffering for event debouncing
- Error modals and dump handling shared between systems
- Effects processing (morph, dispatch, etc.) handled similarly