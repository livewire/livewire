import Message from '@/Message'
import dataGet from 'get-value'
import PrefetchMessage from '@/PrefetchMessage'
import { dispatch, debounce, wireDirectives, walk } from '@/util'
import morphdom from '@/dom/morphdom'
import DOM from '@/dom/dom'
import nodeInitializer from '@/node_initializer'
import store from '@/Store'
import PrefetchManager from './PrefetchManager'
import UploadManager from './UploadManager'
import MethodAction from '@/action/method'
import ModelAction from '@/action/model'
import DeferredModelAction from '@/action/deferred-model'
import MessageBus from '../MessageBus'

export default class Component {
    constructor(el, connection) {
        el.__livewire = this

        this.el = el

        this.lastFreshHtml = this.el.outerHTML

        this.id = this.el.getAttribute('wire:id')

        this.connection = connection

        const initialData = JSON.parse(this.el.getAttribute('wire:initial-data'))
        this.el.removeAttribute('wire:initial-data')

        this.fingerprint = initialData.fingerprint
        this.serverMemo = initialData.serverMemo
        this.effects = initialData.effects

        this.listeners = this.effects.listeners
        this.updateQueue = []
        this.deferredActions = {}
        this.tearDownCallbacks = []
        this.messageInTransit = undefined

        this.scopedListeners = new MessageBus()
        this.prefetchManager = new PrefetchManager(this)
        this.uploadManager = new UploadManager(this)
        this.watchers = {}

        store.callHook('component.initialized', this)

        this.initialize()

        this.uploadManager.registerListeners()

        if (this.effects.redirect) return this.redirect(this.effects.redirect)
    }

    get name() {
        return this.fingerprint.name
    }

    get data() {
        return this.serverMemo.data
    }

    get childIds() {
        return Object.values(this.serverMemo.children).map(child => child.id)
    }

    initialize() {
        this.walk(
            // Will run for every node in the component tree (not child component nodes).
            el => nodeInitializer.initialize(el, this),
            // When new component is encountered in the tree, add it.
            el => store.addComponent(new Component(el, this.connection))
        )
    }

    get(name) {
        // The .split() stuff is to support dot-notation.
        return name
            .split('.')
            .reduce((carry, segment) => carry[segment], this.data)
    }

    updateServerMemoFromResponseAndMergeBackIntoResponse(message) {
        // We have to do a fair amount of object merging here, but we can't use expressive syntax like {...}
        // because browsers mess with the object key order which will break Livewire request checksum checks.

        Object.entries(message.response.serverMemo).forEach(([key, value]) => {
            // Because "data" is "partial" from the server, we have to deep merge it.
            if (key === 'data') {
                Object.entries(value || {}).forEach(([dataKey, dataValue]) => {
                    this.serverMemo.data[dataKey] = dataValue

                    if (message.shouldSkipWatcher()) return

                    // Because Livewire (for payload reduction purposes) only returns the data that has changed,
                    // we can use all the data keys from the response as watcher triggers.
                    Object.entries(this.watchers).forEach(([key, watchers]) => {
                        let originalSplitKey = key.split('.')
                        let basePropertyName = originalSplitKey.shift()
                        let restOfPropertyName = originalSplitKey.join('.')

                        if (basePropertyName == dataKey) {
                            // If the key deals with nested data, use the "get" function to get
                            // the most nested data. Otherwise, return the entire data chunk.
                            let potentiallyNestedValue = !! restOfPropertyName
                                ? dataGet(dataValue, restOfPropertyName)
                                : dataValue

                            watchers.forEach(watcher => watcher(potentiallyNestedValue))
                        }
                    })
                })
            } else {
                // Every other key, we can just overwrite.
                this.serverMemo[key] = value
            }
        })

        // Merge back serverMemo changes so the response data is no longer incomplete.
        message.response.serverMemo = Object.assign({}, this.serverMemo)
    }

    watch(name, callback) {
        if (!this.watchers[name]) this.watchers[name] = []

        this.watchers[name].push(callback)
    }

    set(name, value, defer = false, skipWatcher = false) {
        if (defer) {
            this.addAction(
                new DeferredModelAction(name, value, this.el, skipWatcher)
            )
        } else {
            this.addAction(
                new MethodAction('$set', [name, value], this.el, skipWatcher)
            )
        }
    }

    sync(name, value, defer = false) {
        if (defer) {
            this.addAction(new DeferredModelAction(name, value, this.el))
        } else {
            this.addAction(new ModelAction(name, value, this.el))
        }
    }

    call(method, ...params) {
        return new Promise((resolve, reject) => {
            let action = new MethodAction(method, params, this.el)

            this.addAction(action)

            action.onResolve(thing => resolve(thing))
            action.onReject(thing => reject(thing))
        })
    }

    on(event, callback) {
        this.scopedListeners.register(event, callback)
    }

    addAction(action) {
        if (action instanceof DeferredModelAction) {
            this.deferredActions[action.name] = action

            return
        }

        if (
            this.prefetchManager.actionHasPrefetch(action) &&
            this.prefetchManager.actionPrefetchResponseHasBeenReceived(action)
        ) {
            const message = this.prefetchManager.getPrefetchMessageByAction(
                action
            )

            this.handleResponse(message)

            this.prefetchManager.clearPrefetches()

            return
        }

        this.updateQueue.push(action)

        // This debounce is here in-case two events fire at the "same" time:
        // For example: if you are listening for a click on element A,
        // and a "blur" on element B. If element B has focus, and then,
        // you click on element A, the blur event will fire before the "click"
        // event. This debounce captures them both in the actionsQueue and sends
        // them off at the same time.
        // Note: currently, it's set to 5ms, that might not be the right amount, we'll see.
        debounce(this.fireMessage, 5).apply(this)

        // Clear prefetches.
        this.prefetchManager.clearPrefetches()
    }

    fireMessage() {
        if (this.messageInTransit) return

        Object.entries(this.deferredActions).forEach(([modelName, action]) => {
            this.updateQueue.unshift(action)
        })
        this.deferredActions = {}

        this.messageInTransit = new Message(this, this.updateQueue)

        let sendMessage = () => {
            this.connection.sendMessage(this.messageInTransit)

            store.callHook('message.sent', this.messageInTransit, this)

            this.updateQueue = []
        }

        if (window.capturedRequestsForDusk) {
            window.capturedRequestsForDusk.push(sendMessage)
        } else {
            sendMessage()
        }
    }

    messageSendFailed() {
        store.callHook('message.failed', this.messageInTransit, this)

        this.messageInTransit.reject()

        this.messageInTransit = null
    }

    receiveMessage(message, payload) {
        message.storeResponse(payload)

        if (message instanceof PrefetchMessage) return

        this.handleResponse(message)

        // This bit of logic ensures that if actions were queued while a request was
        // out to the server, they are sent when the request comes back.
        if (this.updateQueue.length > 0) {
            this.fireMessage()
        }

        dispatch('livewire:update')
    }

    handleResponse(message) {
        let response = message.response

        // This means "$this->redirect()" was called in the component. let's just bail and redirect.
        if (response.effects.redirect) {
            this.redirect(response.effects.redirect)

            return
        }

        this.updateServerMemoFromResponseAndMergeBackIntoResponse(message)

        store.callHook('message.received', message, this)

        if (response.effects.html) {
            // If we get HTML from the server, store it for the next time we might not.
            this.lastFreshHtml = response.effects.html

            this.handleMorph(response.effects.html.trim())
        } else {
            // It's important to still "morphdom" even when the server HTML hasn't changed,
            // because Alpine needs to be given the chance to update.
            this.handleMorph(this.lastFreshHtml)
        }

        if (response.effects.dirty) {
            this.forceRefreshDataBoundElementsMarkedAsDirty(
                response.effects.dirty
            )
        }

        if (! message.replaying) {
            this.messageInTransit && this.messageInTransit.resolve()

            this.messageInTransit = null

            if (response.effects.emits && response.effects.emits.length > 0) {
                response.effects.emits.forEach(event => {
                    this.scopedListeners.call(event.event, ...event.params)

                    if (event.selfOnly) {
                        store.emitSelf(this.id, event.event, ...event.params)
                    } else if (event.to) {
                        store.emitTo(event.to, event.event, ...event.params)
                    } else if (event.ancestorsOnly) {
                        store.emitUp(this.el, event.event, ...event.params)
                    } else {
                        store.emit(event.event, ...event.params)
                    }
                })
            }

            if (
                response.effects.dispatches &&
                response.effects.dispatches.length > 0
            ) {
                response.effects.dispatches.forEach(event => {
                    const data = event.data ? event.data : {}
                    const e = new CustomEvent(event.event, {
                        bubbles: true,
                        detail: data,
                    })
                    this.el.dispatchEvent(e)
                })
            }
        }


        store.callHook('message.processed', message, this)
    }

    redirect(url) {
        if (window.Turbolinks && window.Turbolinks.supported) {
            window.Turbolinks.visit(url)
        } else {
            window.location.href = url
        }
    }

    forceRefreshDataBoundElementsMarkedAsDirty(dirtyInputs) {
        this.walk(el => {
            let directives = wireDirectives(el)
            if (directives.missing('model')) return

            const modelValue = directives.get('model').value

            if (DOM.hasFocus(el) && ! dirtyInputs.includes(modelValue)) return

            if (el.wasRecentlyAutofilled) return

            DOM.setInputValueFromModel(el, this)
        })
    }

    addPrefetchAction(action) {
        if (this.prefetchManager.actionHasPrefetch(action)) {
            return
        }

        const message = new PrefetchMessage(this, action)

        this.prefetchManager.addMessage(message)

        this.connection.sendMessage(message)
    }

    handleMorph(dom) {
        this.morphChanges = { changed: [], added: [], removed: [] }

        morphdom(this.el, dom, {
            childrenOnly: false,

            getNodeKey: node => {
                // This allows the tracking of elements by the "key" attribute, like in VueJs.
                return node.hasAttribute(`wire:key`)
                    ? node.getAttribute(`wire:key`)
                    : // If no "key", then first check for "wire:id", then "id"
                    node.hasAttribute(`wire:id`)
                        ? node.getAttribute(`wire:id`)
                        : node.id
            },

            onBeforeNodeAdded: node => {
                //
            },

            onBeforeNodeDiscarded: node => {
                // If the node is from x-if with a transition.
                if (
                    node.__x_inserted_me &&
                    Array.from(node.attributes).some(attr =>
                        /x-transition/.test(attr.name)
                    )
                ) {
                    return false
                }
            },

            onNodeDiscarded: node => {
                store.callHook('element.removed', node, this)

                if (node.__livewire) {
                    store.removeComponent(node.__livewire)
                }

                this.morphChanges.removed.push(node)
            },

            onBeforeElChildrenUpdated: node => {
                //
            },

            onBeforeElUpdated: (from, to) => {
                // Because morphdom also supports vDom nodes, it uses isSameNode to detect
                // sameness. When dealing with DOM nodes, we want isEqualNode, otherwise
                // isSameNode will ALWAYS return false.
                if (from.isEqualNode(to)) {
                    return false
                }

                store.callHook('element.updating', from, to, this)

                // Reset the index of wire:modeled select elements in the
                // "to" node before doing the diff, so that the options
                // have the proper in-memory .selected value set.
                if (
                    from.hasAttribute('wire:model') &&
                    from.tagName.toUpperCase() === 'SELECT'
                ) {
                    to.selectedIndex = -1
                }

                // If the element is x-show.transition.
                if (
                    Array.from(from.attributes)
                        .map(attr => attr.name)
                        .some(
                            name =>
                                /x-show.transition/.test(name) ||
                                /x-transition/.test(name)
                        )
                ) {
                    from.__livewire_transition = true
                }

                let fromDirectives = wireDirectives(from)

                // Honor the "wire:ignore" attribute or the .__livewire_ignore element property.
                if (
                    fromDirectives.has('ignore') ||
                    from.__livewire_ignore === true ||
                    from.__livewire_ignore_self === true
                ) {
                    if (
                        (fromDirectives.has('ignore') &&
                            fromDirectives
                                .get('ignore')
                                .modifiers.includes('self')) ||
                        from.__livewire_ignore_self === true
                    ) {
                        // Don't update children of "wire:ingore.self" attribute.
                        from.skipElUpdatingButStillUpdateChildren = true
                    } else {
                        return false
                    }
                }

                // Children will update themselves.
                if (DOM.isComponentRootEl(from) && from.getAttribute('wire:id') !== this.id) return false

                // Give the root Livewire "to" element, the same object reference as the "from"
                // element. This ensures new Alpine magics like $wire and @entangle can
                // initialize in the context of a real Livewire component object.
                if (DOM.isComponentRootEl(from)) to.__livewire = this

                // If the element we are updating is an Alpine component...
                if (from.__x) {
                    // Then temporarily clone it (with it's data) to the "to" element.
                    // This should simulate backend Livewire being aware of Alpine changes.
                    window.Alpine.clone(from.__x, to)
                }
            },

            onElUpdated: node => {
                this.morphChanges.changed.push(node)

                store.callHook('element.updated', node, this)
            },

            onNodeAdded: node => {
                const closestComponentId = DOM.closestRoot(node).getAttribute('wire:id')

                if (closestComponentId === this.id) {
                    if (nodeInitializer.initialize(node, this) === false) {
                        return false
                    }
                } else if (DOM.isComponentRootEl(node)) {
                    store.addComponent(new Component(node, this.connection))

                    // We don't need to initialize children, the
                    // new Component constructor will do that for us.
                    node.skipAddingChildren = true
                }

                this.morphChanges.added.push(node)
            },
        })
    }

    walk(callback, callbackWhenNewComponentIsEncountered = el => { }) {
        walk(this.el, el => {
            // Skip the root component element.
            if (el.isSameNode(this.el)) {
                callback(el)
                return
            }

            // If we encounter a nested component, skip walking that tree.
            if (el.hasAttribute('wire:id')) {
                callbackWhenNewComponentIsEncountered(el)

                return false
            }

            if (callback(el) === false) {
                return false
            }
        })
    }

    modelSyncDebounce(callback, time) {
        // Prepare yourself for what's happening here.
        // Any text input with wire:model on it should be "debounced" by ~150ms by default.
        // We can't use a simple debounce function because we need a way to clear all the pending
        // debounces if a user submits a form or performs some other action.
        // This is a modified debounce function that acts just like a debounce, except it stores
        // the pending callbacks in a global property so we can "clear them" on command instead
        // of waiting for their setTimeouts to expire. I know.
        if (!this.modelDebounceCallbacks) this.modelDebounceCallbacks = []

        // This is a "null" callback. Each wire:model will resister one of these upon initialization.
        let callbackRegister = { callback: () => { } }
        this.modelDebounceCallbacks.push(callbackRegister)

        // This is a normal "timeout" for a debounce function.
        var timeout

        return e => {
            clearTimeout(timeout)

            timeout = setTimeout(() => {
                callback(e)
                timeout = undefined

                // Because we just called the callback, let's return the
                // callback register to it's normal "null" state.
                callbackRegister.callback = () => { }
            }, time)

            // Register the current callback in the register as a kind-of "escape-hatch".
            callbackRegister.callback = () => {
                clearTimeout(timeout)
                callback(e)
            }
        }
    }

    callAfterModelDebounce(callback) {
        // This is to protect against the following scenario:
        // A user is typing into a debounced input, and hits the enter key.
        // If the enter key submits a form or something, the submission
        // will happen BEFORE the model input finishes syncing because
        // of the debounce. This makes sure to clear anything in the debounce queue.

        if (this.modelDebounceCallbacks) {
            this.modelDebounceCallbacks.forEach(callbackRegister => {
                callbackRegister.callback()
                callbackRegister = () => { }
            })
        }

        callback()
    }

    addListenerForTeardown(teardownCallback) {
        this.tearDownCallbacks.push(teardownCallback)
    }

    tearDown() {
        this.tearDownCallbacks.forEach(callback => callback())
    }

    upload(
        name,
        file,
        finishCallback = () => { },
        errorCallback = () => { },
        progressCallback = () => { }
    ) {
        this.uploadManager.upload(
            name,
            file,
            finishCallback,
            errorCallback,
            progressCallback
        )
    }

    uploadMultiple(
        name,
        files,
        finishCallback = () => { },
        errorCallback = () => { },
        progressCallback = () => { }
    ) {
        this.uploadManager.uploadMultiple(
            name,
            files,
            finishCallback,
            errorCallback,
            progressCallback
        )
    }

    removeUpload(
        name,
        tmpFilename,
        finishCallback = () => { },
        errorCallback = () => { }
    ) {
        this.uploadManager.removeUpload(
            name,
            tmpFilename,
            finishCallback,
            errorCallback
        )
    }

    get $wire() {
        if (this.dollarWireProxy) return this.dollarWireProxy

        let refObj = {}

        let component = this

        return (this.dollarWireProxy = new Proxy(refObj, {
            get(object, property) {
                if (property === 'entangle') {
                    return (name, defer = false) => ({
                        isDeferred: defer,
                        livewireEntangle: name,
                        get defer() {
                            this.isDeferred = true
                            return this
                        },
                    })
                }

                if (property === '__instance') return component

                // Forward "emits" to base Livewire object.
                if (typeof property === 'string' && property.match(/^emit.*/)) return function (...args) {
                    if (property === 'emitSelf') return store.emitSelf(component.id, ...args)

                    return store[property].apply(component, args)
                }

                if (
                    [
                        'get',
                        'set',
                        'sync',
                        'call',
                        'on',
                        'upload',
                        'uploadMultiple',
                        'removeUpload',
                    ].includes(property)
                ) {
                    // Forward public API methods right away.
                    return function (...args) {
                        return component[property].apply(component, args)
                    }
                }

                // If the property exists on the data, return it.
                let getResult = component.get(property)

                // If the property does not exist, try calling the method on the class.
                if (getResult === undefined) {
                    return function (...args) {
                        return component.call.apply(component, [
                            property,
                            ...args,
                        ])
                    }
                }

                return getResult
            },

            set: function (obj, prop, value) {
                component.set(prop, value)

                return true
            },
        }))
    }
}
