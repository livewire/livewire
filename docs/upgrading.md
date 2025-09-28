# Upgrade guide for 3.x -> 4.x

## Method signature changes
- `mount($name, $params = [], $key = null)` -> `mount($name, $params = [], $key = null, $slots = [])`
- `stream($name, $content, $replace = false)` -> `stream($content, $replace, $name)`

## Config changes
- New `livewire.component_locations` to define view-based component locations
- New `livewire.component_namespaces` to define view-based component namespaces
- `livewire.layout` -> `livewire.component_layout` (Was 'components.layouts.app'. Now is 'layouts::app')
- `livewire.lazy_placeholder` -> `livewire.component_placeholder`
- `make_command.type` ('sfc', 'mfc', or 'class')
- `make_command.emoji` (weather to use the emoji prefix or not)

## JavaScript Hook Changes

### Deprecated: `commit` and `request` hooks

The `commit` and `request` hooks have been deprecated in favor of the new interceptor system, which provides more granular control and better performance.

#### Migrating from `commit` hook

The old `commit` hook:

```js
// OLD - Deprecated
Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
    respond(() => {
        // Runs after response received but before processing
    })

    succeed(({ snapshot, effects }) => {
        // Runs after successful response
    })

    fail(() => {
        // Runs if request failed
    })
})
```

Should be replaced with the new `interceptMessage`:

```js
// NEW - Recommended
Livewire.interceptMessage(({ component, message, onFinish, onSuccess, onError, onFailure }) => {
    onFinish(() => {
        // Equivalent to respond()
    })

    onSuccess(({ payload }) => {
        // Equivalent to succeed()
        // Access snapshot via payload.snapshot
        // Access effects via payload.effects
    })

    onError(() => {
        // Equivalent to fail() for server errors
    })

    onFailure(() => {
        // Equivalent to fail() for network errors
    })
})
```

#### Migrating from `request` hook

The old `request` hook:

```js
// OLD - Deprecated
Livewire.hook('request', ({ url, options, payload, respond, succeed, fail }) => {
    respond(({ status, response }) => {
        // Runs when response received
    })

    succeed(({ status, json }) => {
        // Runs on successful response
    })

    fail(({ status, content, preventDefault }) => {
        // Runs on failed response
    })
})
```

Should be replaced with the new `interceptRequest`:

```js
// NEW - Recommended
Livewire.interceptRequest(({ request, onResponse, onSuccess, onError, onFailure }) => {
    // Access url via request.uri
    // Access options via request.options
    // Access payload via request.payload

    onResponse(({ response }) => {
        // Equivalent to respond()
        // Access status via response.status
    })

    onSuccess(({ response, responseJson }) => {
        // Equivalent to succeed()
        // Access status via response.status
        // Access json via responseJson
    })

    onError(({ response, responseBody, preventDefault }) => {
        // Equivalent to fail() for server errors
        // Access status via response.status
        // Access content via responseBody
    })

    onFailure(({ error }) => {
        // Equivalent to fail() for network errors
    })
})
```

#### Key differences

1. **More granular error handling**: The new system separates network failures (`onFailure`) from server errors (`onError`)
2. **Better lifecycle hooks**: Message interceptors provide additional hooks like `onSync`, `onMorph`, and `onRender`
3. **Cancellation support**: Both messages and requests can be cancelled/aborted
4. **Component scoping**: Interceptors can be scoped to specific components using `Livewire.intercept($wire, ...)`

For complete documentation on the new interceptor system, see the [JavaScript Interceptors documentation](/docs/javascript#interceptors).

## Pre-release questions
- Should we make `$refs.modal.dispatch('close')` be { bubbles: false } by default when $wire is accessed through `$ref` or `$parent`? Instead of needing `dispatchSelf()`
