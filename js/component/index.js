import Message from '@/Message'
import PrefetchMessage from '@/PrefetchMessage'
import { dispatch, debounce, walk } from '@/util'
import morphdom from '@/dom/morphdom'
import DOM from '@/dom/dom'
import DOMElement from '@/dom/dom_element'
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
        el.rawNode().__livewire = this

        this.id = el.getAttribute('id')

        this.connection = connection

        const initialData = JSON.parse(this.el.getAttribute('initial-data'))
        this.el.removeAttribute(name)

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

        store.callHook('componentInitialized', this)

        this.initialize()

        this.uploadManager.registerListeners()

        if (this.effects.redirect) return this.redirect(this.effects.redirect)
    }

    get el() {
        return DOM.getByAttributeAndValue('id', this.id)
    }

    get name() {
        return this.fingerprint.name
    }

    get data() {
        return this.serverMemo.data
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

    updateDataAndMemo(newData, newMemo) {
        Object.entries(newData || {}).forEach(([key, value]) => {
            let oldValue = this.serverMemo.data[key]

            if (oldValue !== undefined && oldValue !== value) {
                this.serverMemo.data[key] = value

                let watchers = this.watchers[key] || []

                watchers.forEach(watcher => watcher(value))
            }
        })

        // Only update the memo properties that exist in the returning payload.
        Object.entries(newMemo).forEach(([key, value]) => {
            if (key === 'data') return

            this.serverMemo[key] = value
        })
    }

    watch(name, callback) {
        if (!this.watchers[name]) this.watchers[name] = []

        this.watchers[name].push(callback)
    }

    set(name, value) {
        this.addAction(new MethodAction('$set', [name, value], this.el))
    }

    sync(name, value) {
        this.addAction(new ModelAction(name, value, this.el))
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

            this.handleResponse(message.response)

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

            store.callHook('messageSent', this, this.messageInTransit)

            this.updateQueue = []
        }

        if (window.capturedRequestsForDusk) {
            window.capturedRequestsForDusk.push(sendMessage)
        } else {
            sendMessage()
        }
    }

    messageSendFailed() {
        store.callHook('messageFailed', this)

        this.messageInTransit.reject()

        this.messageInTransit = null
    }

    receiveMessage(message, payload) {
        var response = message.storeResponse(payload)

        if (message instanceof PrefetchMessage) return

        this.handleResponse(response)

        // This bit of logic ensures that if actions were queued while a request was
        // out to the server, they are sent when the request comes back.
        if (this.updateQueue.length > 0) {
            this.fireMessage()
        }

        dispatch('livewire:update')
    }

    handleResponse(response) {
        this.updateDataAndMemo(response.serverMemo.data, response.serverMemo)

        store.callHook('responseReceived', this, response)

        // This means "$this->redirect()" was called in the component. let's just bail and redirect.
        if (response.effects.redirect) {
            this.redirect(response.effects.redirect)

            return
        }

        store.callHook('responseReceived', this, response)

        if (response.effects.html) {
            this.replaceDom(response.effects.html)
        }

        if (response.effects.dirty) {
            this.forceRefreshDataBoundElementsMarkedAsDirty(
                response.effects.dirty
            )
        }

        this.messageInTransit.resolve()

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
                this.el.el.dispatchEvent(e)
            })
        }
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
            if (el.directives.missing('model')) return

            const modelValue = el.directives.get('model').value

            if (el.isFocused() && !dirtyInputs.includes(modelValue)) return

            el.setInputValueFromModel(this)
        })
    }

    replaceDom(rawDom) {
        let objectContainingRawDomToFakePassingByReferenceToBeAbleToMutateFromWithinAHook = {
            html: rawDom,
        }
        store.callHook(
            'beforeDomUpdate',
            this,
            objectContainingRawDomToFakePassingByReferenceToBeAbleToMutateFromWithinAHook
        )

        this.handleMorph(
            objectContainingRawDomToFakePassingByReferenceToBeAbleToMutateFromWithinAHook.html.trim()
        )

        store.callHook('afterDomUpdate', this)
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

        morphdom(this.el.rawNode(), dom, {
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
                const el = new DOMElement(node)

                store.callHook('elementRemoved', el, this)

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

                store.callHook('beforeElementUpdate', from, to, this)

                const fromEl = new DOMElement(from)

                // Reset the index of wire:modeled select elements in the
                // "to" node before doing the diff, so that the options
                // have the proper in-memory .selected value set.
                if (
                    fromEl.hasAttribute('model') &&
                    fromEl.rawNode().tagName.toUpperCase() === 'SELECT'
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

                // Honor the "wire:ignore" attribute or the .__livewire_ignore element property.
                if (
                    fromEl.directives.has('ignore') ||
                    from.__livewire_ignore === true ||
                    from.__livewire_ignore_self === true
                ) {
                    if (
                        (fromEl.directives.has('ignore') &&
                            fromEl.directives
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
                if (
                    fromEl.isComponentRootEl() &&
                    fromEl.getAttribute('id') !== this.id
                )
                    return false

                // If the element we are updating is an Alpine component...
                if (from.__x) {
                    // Then temporarily clone it (with it's data) to the "to" element.
                    // This should simulate backend Livewire being aware of Alpine changes.
                    window.Alpine.clone(from.__x, to)
                }
            },

            onElUpdated: node => {
                this.morphChanges.changed.push(node)

                store.callHook('afterElementUpdate', node, this)
            },

            onNodeAdded: node => {
                const el = new DOMElement(node)

                const closestComponentId = el.closestRoot().getAttribute('id')

                if (closestComponentId === this.id) {
                    if (nodeInitializer.initialize(el, this) === false) {
                        return false
                    }
                } else if (el.isComponentRootEl()) {
                    store.addComponent(new Component(el, this.connection))

                    // We don't need to initialize children, the
                    // new Component constructor will do that for us.
                    node.skipAddingChildren = true
                }

                this.morphChanges.added.push(node)
            },
        })
    }

    walk(callback, callbackWhenNewComponentIsEncountered = el => {}) {
        walk(this.el.rawNode(), node => {
            const el = new DOMElement(node)

            // Skip the root component element.
            if (el.isSameNode(this.el)) {
                callback(el)
                return
            }

            // If we encounter a nested component, skip walking that tree.
            if (el.isComponentRootEl()) {
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
        let callbackRegister = { callback: () => {} }
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
                callbackRegister.callback = () => {}
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
                callbackRegister = () => {}
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
        finishCallback = () => {},
        errorCallback = () => {},
        progressCallback = () => {}
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
        finishCallback = () => {},
        errorCallback = () => {},
        progressCallback = () => {}
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
        finishCallback = () => {},
        errorCallback = () => {}
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
                    return name => ({ livewireEntangle: name })
                }

                // Forward public API methods right away.
                if (['get', 'set', 'call', 'on'].includes(property)) {
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
                if (window.Alpine) {
                    // This prevents a "blip" when using x-model to set a Livewire property.
                    Alpine.ignoreFocusedForValueBinding = true
                }

                component.set(prop, value)

                return true
            },
        }))
    }
}
