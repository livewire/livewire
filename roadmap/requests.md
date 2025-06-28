## Concrete things we want
- [x] `wire:poll` to be non-blocking and cancellable by default
- [x] `wire:model.live` should cancel any existing component requests
- [x] Triggering an action `wire:click` should cancel any existing component requests
- [x] different components can make parallel requests
- [ ] Streamed responses can be intentionally stopped
- [x] `wire:navigate` should cancel all existing requests - component or navigate requests
- [ ] `wire:navigate` should be able to be halted if there is a dirty form or a form is submitting
- [ ] we can make static calls to a component
- [ ] static action calls should not cancel other static action calls

## Tasks
- [ ] Ensure that if a component message is cancelled, that `wire:loading` is stopped
- [ ] If a component message is cancelled because a new component message is sent, re-process `wire:loading`
- [ ] If a component message responds with a navigate redirect, then don't stop `wire:loading` instead, continue with the navigate request

1. Rename all of the things
2. Merge it
3. Deep dive on `wire:loading`

## Ways to trigger an action:
* Event listener like `wire:click` & `wire:submit`
* Dispatching events via `$dispatch`
* Uploading a file
* Calling an action inside a poll: `wire:poll="someAction"`
* JS manually calling `$wire.someAction(...)`
* lazy loading `__lazyLoad`

## Ways to trigger a commit:
* `wire:click="$refresh|$set|$toggle"`
* Empty `wire:poll`
* `wire:model.live`
* JS `$wire.$commit` or `$wire.$set(...)`

## Proposal

The proposal is to split the system into two distinct pieces:
- request types
- component updates

Currently the component update handling is tied heavily into the request system, but they are two distinct pieces that should communicate.

### Request types

The proposal is to have different request types:
- PageRequest - when changing pages via `wire:navigate`
- UpdateRequest - component update request
- StaticRequest - component static method call request
- FileUploadRequest - the request that actually uploads the file

It would be nice if the different request types (PageRequest, StaticRequest, etc.) could live in their own feature folders.

The different requests will hit different URLs/ endpoints based on their own definition:
- PageRequest - hits `/the-page-url`
- UpdateRequest - hits `/livewire/update`
- StaticRequest - hits `/livewire/static`
- FileUploadRequest - hits `/livewire/upload`

Each request "type" can control what endpoint it hits, what data it needs, the request type, even the request methodology (like `XMLHttpRequest()` for files and `fetch()` for pages or updates).
This is all contained within the specific request type class.

All the requests will be controlled using a `RequestManager`.

The request manager will be responsible for keeping track of any outstanding requests.

This will allow us strategically cancel requests based on the types of outstandig requests in the request manager.

### Component update

A component update can consist of multiple pieces:
- Property updates
- Action calls
- `$refresh`/ `wire:poll`/ empty calls

But a call to the `update` endpoint can also contain updates for multiple components at the same time.

In v3 we used the term `commit` to define the collection of property updates/ action calls per component. So each commit should only have one component associated with it.

Then commits for different components could be added to the same request to be sent to the server.

The proposal is to have an `UpdateManager` who's job it is to track any component updates/ calls.

It acts as a buffer between the component and the request system.

A component update "collection" is now to be called a `ComponentMessage` which consists of the property updates, action calls, and empty calls.

The `UpdateManager`'s responsibility is to take any component messages and massage them into a `UpdateRequest`. An `UpdateRequest` can contain multiple `ComponentMessages`.

**Flow**

`$wire.call(method, params)`
    ⬇
`updateManager.addCall(method, parms)`
    ⬇
`updateManager.prepareRequests()`
    ⬇
`message.prepare()`
    ⬇
`new UpdateRequest()`
    ⬇
`request.addMessage(message)`
    ⬇
`requestManager.add(request)`
    ⬇
`request.send()`
    ⬇
`request.succeed()`
    ⬇
`message.succeed()`


### Navigate request

Currently the navigate feature has been designed as an Alpine plugin. So the goal would be to try and keep it as distinct as possible.

The ideal solution is we can swap out the contents of the `js/plugins/navigate/fetch.js` with an implementation that makes use of the `requestManager` and a custom `PageRequest` class to perform any navigate requests.

Looking at the navigate package, the only place where Livewire specific javascript code (external to navigate feature) has been included as a dependency is in `fetch.js` and `bar.js`.

Based on that I believe it is acceptable to include a reference to the `RequestManager` inside `fetch.js`. It would be the only external reference.

**The question is:** should the `PageRequest` class define which other requests it can cancel or should we instead have a numeric priority level system within the `RequestManager` so the `PageRequest` inside the navigate feature doesn't need to know anything about the other types of requests?

**Flow**

`fetchHtml(destination)`
    ⬇
`performFetch(uri)`
    ⬇
`new PageRequest(uri)`
    ⬇
`requestManager.add(request)`
    ⬇
`request.send()`
    ⬇
`request.succeed()`

