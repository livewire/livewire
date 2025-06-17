## Concrete things we want

### Bailable `wire:poll`

Poll requests in a Livewire component currently hold up the component and block other requests until they return. This is surprising to users and problematic when the poll requests are lengthy.

```html
<div wire:poll>
    <!-- ... -->
</div>
```

Question: should wire:poll be "bailable" by default?
Answer: Yes. lol.

Question: should a wire:poll have an opt-in to be NOT bailable?
Answer: Maybe? probably not initially

Question: if we DID have it opt-out, what would that API be?

```html
<div wire:poll.uninteruptible>
    <!-- ... -->
</div>
```

Essentially, the current V3 system does this:

```js
setInterval(() => {
    $wire.$commit()
})
```

We need an API to make $wire calls marked as interuptible?
Could be a callback?
Or maybe any .$commit() requests are interruptible?

Two paths forward:
* Prioritization system within request bus
    * Numeric priority levels? (1-5)
    * Taxonomic priority levels?
        * Action (user-triggered request: via click or something like that)
        * Passive (background requests like wire:poll)


Concepts:
* Levels of request granularity:
    * least granular: has multiple components
    * next: has single component but multiple "actions"
    * next: has single component and one "action"
    * next: has single componet but multiple "updates"
    * next: has single componet but one "updates"
    * more granular: is a $refresh

Naming:
- Request
    - Pool
        - Commit
            [Commit types]:
            - Action
            - Commit

Ways to trigger an action:
* Event listener like `wire:click` & `wire:submit`
* Dispatching events via `$dispatch`
* Uploading a file
* Calling an action inside a poll: `wire:poll="someAction"`
* JS manually calling `$wire.someAction(...)`
* lazy loading `__lazyLoad`

Ways to trigger a commit:
* `wire:click="$refresh|$set|$toggle"`
* Empty `wire:poll`
* `wire:model.live`
* JS `$wire.$commit` or `$wire.$set(...)`

Scenarios where we want a request to be interrupted:
* Typing into `wire:model.live` with a debounce (real-time searching)
* Clicking an action while a `wire:poll` is out
* Streamed response should be cancelled by force (with something like `$stop`) or by another action
* `wire:navigate` is pressed
* A button is pressed

Scenarios where we want a request to NOT be interrupted:
* Not navigating away (via `wire:navigate`) while a form is submitting...


* Parrallel (static) action

Levels of request:

Rule: each level interrupts all the levels below itself unless meeting specific conditions

* Page Navigation
* Submission
    * Interrupts page navigation with prompt from `wire:submit wire:navigate.confirm="Are you sure you want to navigate away while this form is submitting?"`...
* User-triggerd action
* System-triggered Action
* Update
* Refresh

* Page Navigation: Blocking
* Blocking: Submission
    * Interrupts page navigation with prompt from `wire:submit wire:navigate.confirm="Are you sure you want to navigate away while this form is submitting?"`...
* User-triggerd action
* System-triggered Action
* Non-blocking: Update
* Non-blocking: Refresh

Request qualities:
* blocking vs non-blocking

Prioritization rules:
* Page navigate
* Commits interrupt other commits?
* Stops interrupt everything

Concepts:
* Request priorities
* Blocking vs non-blocking
* Response resolution strategies



# Request flow

Component gets property updates.

When ready, component can trigger a request.

A request can also be triggered from an action like `wire:click`.

But if there are any updates, they are added to the request first and then the action is added.

`$wire` -> RequestBus -> prepare/trigger/send/sendAction

What orchestrates the pooling/ request handling? Does the requestBus/ requestManager do everything?

Do we have:
- PageRequest - for page level changes, like `wire:navigate`
- PoolRequest - for pooled `ComponentRequests`
- ComponentRequest - for component updates and action calls
- StaticRequest - for static calls to a specific component

Then we can have a RequestDispatcher/RequestCoordinator which keeps track of requests to be sent, inflight requests, resolving requests, error handling.

So how do we distinguish between submissions, user triggered actions, and system actions?

What about an empty ComponentRequest?

A ComponentRequest can have:
- updates
- calls
- priority?
- isBlocking?

```js
let requestManager = new RequestManager()

requestManager.stageUpdate(component)
requestManager.stageCall(component, method, params)
```

Staging an update/call starts the 5ms buffer.

Once the buffer is complete, the `RequestManager` takes any pending ComponentRequests and creates a PoolRequest from them.

The `RequestManager` can then instruct the pool to send, and it keeps track of it in:
```js
let inflightRequests = [] // Will contain Page/Pool/Static requests. Not `ComponentRequests`
```

But how does the request manager know if any `ComponentRequests` are currently in flight? Do we also add the `ComponentRequests` to the `inflightRequests`?

The benefit of this is that we can easily mark a `ComponentRequest` as stale, so it doesn't resolve when the `PoolRequest` is finished. We can also cancel a `PoolRequest`.

Maybe a `ComponentRequest` should know if it is part of a `PoolRequest` and if it is and it's marked as stale, and it's the only `ComponentRequest` in the Pool, the Pool gets cancelled too. Or maybe it just instructs the pool that it is stale/cancelled. The pool can deal with itself.


So if a `requestManager.pageRequest(url, options)` is fired:
- if there is an existing `PageRequest` cancel it
- if there are any existing `PoolRequests` cancel them
- if there are any existing `ComponentRequests` cancel them
- if there are any existing `StaticRequests` cancel them

If `requestManager.stageUpdate/stageCall` is fired:
- if there is an existing `PageRequest` then cancel the stage update/call
- if there are any existing `PoolRequests` then do nothing as this will be handled by the component requests
- if there are any existing `ComponentRequests` then search them to find if the current component is in one of them and if it is, then cancel the one it is already in. This should also cancel the pool if the pool only has this commit in it
- if there are any existing `StaticRequests` then let them continue

If `requestManager.staticRequest(component, method, params)` is fired:
- if there is an existing `PageRequest` then cancel the static request call
- if there are any existing `PoolRequests` then do nothing, these can continue
- if there are any existing `ComponentRequests` then do nothing, these can continue
- if there are any existing `StaticRequests` then search them to find if the current component is in one of them and the static method is being called, and if it is, then cancel the one it is already in. **Do we want this** Yes I think so. But multiple static component calls can be made and they are not pooled, they are isolated by default

What do we do if a `ComponentRequest` returns with a navigate redirect?


A Request class should define a `cancels()` method:

```js
// PageRequest cancels method
function cancels()
{
    return [
        'PageRequest',
        'PoolRequest',
        'StaticRequest',
    ]
}
```

The above example is for a `PageRequest`.

The reason we want to do it that way, is so that a PageRequest is the top most request, but if we removed it from the system, then the other request types shouldn't care that it is missing.

It would be cool if `PageRequest` lived in the navigate feature folder and `StaticRequest` lives in the static feature folder.

But `ComponentRequest` and `PoolRequest` I think need to be treated a little differently.

Maybe a `ComponentRequest` can also be standalone or also in a pool?

I wonder if the type of request depends on which endpoint it hits? Like:
- PageRequest - hits `/the-page-url`
- UpdateRequest - hits `/livewire/update`
- StaticRequest - hits `/livewire/static`

Then an update request can contain one or more component updates.

This would also work for a `FileRequest`.

Each request "type" can control what endpoint it hits, what data it needs, the request type, even the request methodology (like `XMLHttpRequest()` for files).

Would there be a benefit to having like `S3FileRequest`??? Or `SideLoadFileRequest`??

These request types would all inherit from a base `Request` class that defines some methods. But each of these can live in their feature folders.

How does navigate interact with this, with Alpine controlling the navigate request at the moment?

Maybe it should build up it's own `NavigateRequest` or `PageRequest` and then pass it to `Alpine.request.send(pageRequest)`?

What if we have:
- PageRequest
- UpdateRequest/ComponentRequest
- StaticRequest

File upload requests:
- FileUploadStartRequest
- FileUploadRequest
- FileUploadFinishRequest
- FileUploadErrorRequest
- FileUploadRemoveRequest

And an UpdateRequest contains one or many `ComponentMessage`

PageRequest params:
- url
- options

UpdateRequest params:
- components - array of component updates/ messages
    ComponentMessage params:
    - component
    - snapshot - JSON encoded snapshot
    - updates - any property updates
    - calls - any method calls
- _token - CSRF token

StaticRequest
- component
- method
- params

Now we can have an `UpdateManager` for component level updates and a `RequestManager` for request level control.

`UpdateManager` and `RequestManager` should be singletons on the page.

Ok so how does a component message check with the existing UpdateRequests, to see if an UpdateRequest has a component message in it, and if it does, cancels it if required?

Well the UpdateRequest can call the request manager and instruct it which Requests to cancel.