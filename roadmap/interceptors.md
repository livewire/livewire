## Interceptors

Interceptors are a new way in Livewire v4 to hook into the javascript lifecycle of a request.

There are three types of interceptors:
- Global interceptor
- Component interceptor
- Action interceptor

### Global interceptor

A global interceptor will be fired for all components.

You can register it using the global Livewire object.

```js
Livewire.intercept(() => {})
```

### Component interceptor

A component interceptor will only be fired for the component it has been registered on.

You can register it by calling intercept on a component instance.

```js
this.intercept(() => {})
```

### Action interceptor

An action interceptor is scoped to the component it is registered on and will only be fired for an action matching the provided name.

You can register it by calling intercept on a component instance and by passing in an action name as the first parameter.

```js
this.intercept('foo', () => {})
```

### Interceptor callback

Each interceptor is passed a callback, which can be used to hook into different parts of the request lifecycle.

```js
component.intercept(({ onSend, onCancel, onError, onSuccess }) => {
    onSend()
    onCancel()
    onError()
    onSuccess(({ onSync, onMorph, onRender }) => {
        //
    })
})

Livewire.interceptMessage(({ message, queuedRequests, inFlightRequests }) => {

// an incrementing counter
// a toggling like emoji
// a save button
// a refresh button

// hit 3 times scenario:
// A) Allow both messages async
// B) Cancel me (new message)
// C) Cancel previous message
// D) Queue up all actions inside one message on response received and squash identical actions

//

// 1) We're creating a new message
// 2) We waited for the buffer and have a concrete message with all actions
    // 2.1) We can prompt the outside to re-arrange or add new messages
    // 2.2) Classify the message as a type
// 3) We have partitioned them into requests
// 4) We have finalized the payloads

// queueing a message till another one comes back
interceptAction(({ action, otherScopedMessages, blockMe, queueMe }) => {
    // find the message

    message.afterFinish(() => {
        fireActionDirectlyAndSquashIdenticalActions(action)
    })
})

// wire:poll (all passive types of actions)
interceptAction(({ action, otherScopedMessages, blockMe, queueMe }) => {
    //
})

// wire:model.live
interceptAction(({ action, otherScopedMessages, blockMe, queueMe }) => {
    message.cancel()
})

// squash identical actions
beforePartition(({ message, putOntoNewRequest }) => {
    message.actions // loop through and make sure only one of each fingerprint is here...
})

// basic isolation
beforePartition(({ message, putOntoNewRequest }) => {
    if (message.component.isIsolated()) {
        putOntoNewRequest()
    }
})

// modelable/reactive
beforePartition(({ message, otherMessages, putOntoNewRequest }) => {
    let collectedMesages = []

    getDeepChildrenWithBindings(message.component, child => {
        collectedMessages.push(
            fireActionSkipBufferAndReturnMessage(child, '$commit')
        )
    })

    putOntoNewRequest([message, ...collectedMessages])
})

// lazy loading
beforePartition(({ message, putOntoNewRequest }) => {
    if (message.component.isLazyIsolated() && ! message.component.isIsolated()) {
        putOntoNewRequest()
    }
})

beforePartition(({ messages, addIntoNewRequest }) => {
    messages.forEach(message => {
        if (message.isIsolated()) {
            addIntoNewRequest()
        }
    })
})

onPartition(({ requests }) => {
    requests.forEach(request => {
        request.messages.forEach(message => {
            if (message.isIsolated()) {
                makeSureThisMessageTravelsSolo()
            }
        })
    })
})


    // different message types:
    // compound message (waits for inflights, blocks )
    // poll (poll)
    // data update (wire:model.live)
    // lazy load
    // wire:model.live
    // wire:click action / $refresh

    // Is this a parallel action?
        // skip buffering, put it in a message and send it off as a single request
    // Is there a message already out for this component?
        // are the
            // Put it in it's own request
    //

})

Livewire.interceptRequest(({ request, messages }) => {
    onSend()
    onCancel()
    onFailure()
    onResponse(({ response, onParsed }) => {
        //
    })
})

Livewire.interceptTransmission(({
    requests,
    inFlightRequests
}) => {


})

Livewire.intercept(({ request }) => {
    // Before anything code...

    request.beforeSend(({ component, payload }) => {}) // access to compiled payload
    request.afterSend(({ component, payload }) => {}) // good place to run code knowing that payload is in-flight

    request.beforeResponse(({ component, response }) => {}) // good place to analyze the response payload before anything has been handled at all
    request.afterResponse(({ component, response }) => {}) // place to run code knowing snapshots have been merged

    request.beforeRender(({ component }) => {}) // good place to do anything with the pre-morphed HTML state

    request.beforeMorph(({ component, el, html }) => {}) // same as beforeRender
    request.afterMorph(({ component, el, html }) => {}) // good place to interact with the HTML before the setTimeout

    request.afterRender(({ component }) => {}) // runs after the setTimeout

    // Common ones...
    request.cancel() // cancel request in-flight or pre-flight
    request.onError(({ e }) => {}) // some kind of error of any kind happened in the request (and you can access the status code)
    request.onFailure(({ response, content }) => {}) // the actual fetch request failed for some reason
    request.onSuccess(({ response }) => {}) // inverse of onError
    request.onCancel(() => {}) // if request.cancel was called or polls or something were interrupted for any reason

    return () => {
        // After everything code
    }
})
```