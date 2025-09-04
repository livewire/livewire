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
this.intercept(({ el, directive, component, request }) => {
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