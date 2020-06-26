import Message from '@/Message'
import PrefetchMessage from '@/PrefetchMessage'
import { debounce, walk } from '@/util'
import morphdom from '@/dom/morphdom'
import DOM from '@/dom/dom'
import DOMElement from '@/dom/dom_element'
import nodeInitializer from '@/node_initializer'
import store from '@/Store'
import PrefetchManager from './PrefetchManager'
import EchoManager from './EchoManager'
import UploadManager from './UploadManager'
import MethodAction from '@/action/method'
import ModelAction from '@/action/model'
import MessageBus from '../MessageBus'

export default class Component {
    constructor(el, connection) {
        el.rawNode().__livewire = this
        this.id = el.getAttribute('id')
        const initialData = JSON.parse(this.extractLivewireAttribute('initial-data'))
        this.data = initialData.data || {}
        this.events = initialData.events || []
        this.children = initialData.children || {}
        this.checksum = initialData.checksum || ''
        this.name = initialData.name || ''
        this.errorBag = initialData.errorBag || {}
        this.redirectTo = initialData.redirectTo || false
        this.scopedListeners = new MessageBus,
        this.connection = connection
        this.actionQueue = []
        this.messageInTransit = null
        this.modelTimeout = null
        this.tearDownCallbacks = []
        this.prefetchManager = new PrefetchManager(this)
        this.echoManager = new EchoManager(this)
        this.uploadManager = new UploadManager(this)

        store.callHook('componentInitialized', this)

        this.initialize()

        this.echoManager.registerListeners()
        this.uploadManager.registerListeners()

        if (this.redirectTo) {
            this.redirect(this.redirectTo)

            return
        }
    }

    get el() {
        return DOM.getByAttributeAndValue('id', this.id)
    }

    get root() {
        return this.el
    }

    extractLivewireAttribute(name) {
        const value = this.el.getAttribute(name)

        this.el.removeAttribute(name)

        return value
    }

    initialize() {
        this.walk(el => {
            // Will run for every node in the component tree (not child component nodes).
            nodeInitializer.initialize(el, this)
        }, el => {
            // When new component is encountered in the tree, add it.
            store.addComponent(
                new Component(el, this.connection)
            )
        })
    }

    get(name) {
        // The .split() stuff is to support dot-notation.
        return name.split('.').reduce((carry, dotSeperatedSegment) => carry[dotSeperatedSegment], this.data)
    }

    set(name, value) {
        this.addAction(new ModelAction(name, value, this.el))
    }

    call(method, ...params) {
        this.addAction(new MethodAction(method, params, this.el))
    }

    on(event, callback) {
        this.scopedListeners.register(event, callback)
    }

    addAction(action) {
        if (this.prefetchManager.actionHasPrefetch(action) && this.prefetchManager.actionPrefetchResponseHasBeenReceived(action)) {
            const message = this.prefetchManager.getPrefetchMessageByAction(action)

            this.handleResponse(message.response)

            this.prefetchManager.clearPrefetches()

            return
        }

        this.actionQueue.push(action)

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

        this.messageInTransit = new Message(
            this,
            this.actionQueue
        )

        this.connection.sendMessage(this.messageInTransit)

        store.callHook('messageSent', this, this.messageInTransit)

        this.actionQueue = []
    }

    messageSendFailed() {
        store.callHook('messageFailed', this)

        this.messageInTransit = null
    }

    receiveMessage(payload) {
        var response = this.messageInTransit.storeResponse(payload)

        this.handleResponse(response)

        // This bit of logic ensures that if actions were queued while a request was
        // out to the server, they are sent when the request comes back.
        if (this.actionQueue.length > 0) {
            this.fireMessage()
        }
    }

    handleResponse(response) {
        this.data = response.data
        this.checksum = response.checksum
        this.children = response.children
        this.errorBag = response.errorBag

        // This means "$this->redirect()" was called in the component. let's just bail and redirect.
        if (response.redirectTo) {
            this.redirect(response.redirectTo)

            return
        }

        store.callHook('responseReceived', this, response)

        this.replaceDom(response.dom, response.dirtyInputs)

        this.forceRefreshDataBoundElementsMarkedAsDirty(response.dirtyInputs)

        this.messageInTransit = null

        if (response.eventQueue && response.eventQueue.length > 0) {
            response.eventQueue.forEach(event => {
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

        if (response.dispatchQueue && response.dispatchQueue.length > 0) {
            response.dispatchQueue.forEach(event => {
                const data = event.data ? event.data : {}
                const e = new CustomEvent(event.event, { bubbles: true, detail: data});
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

            if (el.isFocused() && ! dirtyInputs.includes(modelValue)) return

            el.setInputValueFromModel(this)
        })
    }

    replaceDom(rawDom) {
        let objectContainingRawDomToFakePassingByReferenceToBeAbleToMutateFromWithinAHook = { html: rawDom }
        store.callHook('beforeDomUpdate', this, objectContainingRawDomToFakePassingByReferenceToBeAbleToMutateFromWithinAHook)

        this.handleMorph(objectContainingRawDomToFakePassingByReferenceToBeAbleToMutateFromWithinAHook.html.trim())

        store.callHook('afterDomUpdate', this)
    }

    addPrefetchAction(action) {
        if (this.prefetchManager.actionHasPrefetch(action)) {
            return
        }

        const message = new PrefetchMessage(
            this,
            action,
        )

        this.prefetchManager.addMessage(message)

        this.connection.sendMessage(message)
    }

    receivePrefetchMessage(payload) {
        this.prefetchManager.storeResponseInMessageForPayload(payload)
    }

    handleMorph(dom) {
        this.morphChanges = { changed: [], added: [], removed: [] }

        morphdom(this.el.rawNode(), dom, {
            childrenOnly: false,

            getNodeKey: node => {
                // This allows the tracking of elements by the "key" attribute, like in VueJs.
                return node.hasAttribute(`wire:key`)
                    ? node.getAttribute(`wire:key`)
                    // If no "key", then first check for "wire:id", then "id"
                    : (node.hasAttribute(`wire:id`)
                        ? node.getAttribute(`wire:id`)
                        : node.id)
            },

            onBeforeNodeAdded: node => {
                //
            },

            onBeforeNodeDiscarded: node => {
                //
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

                // Honor the "wire:ignore" attribute or the .__livewire_ignore element property.
                if (fromEl.directives.has('ignore') || from.__livewire_ignore === true || from.__livewire_ignore_self === true) {
                    if ((fromEl.directives.has('ignore') && fromEl.directives.get('ignore').modifiers.includes('self')) || from.__livewire_ignore_self === true) {
                        // Don't update children of "wire:ingore.self" attribute.
                        from.skipElUpdatingButStillUpdateChildren = true
                    } else {
                        return false;
                    }
                }

                // Children will update themselves.
                if (fromEl.isComponentRootEl() && fromEl.getAttribute('id') !== this.id) return false

                // If the element we are updating is an Alpine component...
                if (from.__x) {
                    // Then temporarily clone it (with it's data) to the "to" element.
                    // This should simulate backend Livewire being aware of Alpine changes.
                    window.Alpine.clone(from.__x, to)
                }
            },

            onElUpdated: (node) => {
                this.morphChanges.changed.push(node)

                store.callHook('afterElementUpdate', node, this)
            },

            onNodeAdded: (node) => {
                if (node.tagName.toLowerCase() === 'script') {
                    eval(node.innerHTML)
                    return false
                }

                const el = new DOMElement(node)

                const closestComponentId = el.closestRoot().getAttribute('id')

                if (closestComponentId === this.id) {
                    nodeInitializer.initialize(el, this)
                } else if (el.isComponentRootEl()) {
                    store.addComponent(
                        new Component(el, this.connection)
                    )

                    // We don't need to initialize children, the
                    // new Component constructor will do that for us.
                    node.skipAddingChildren = true
                }

                this.morphChanges.added.push(node)
            },
        })
    }

    walk(callback, callbackWhenNewComponentIsEncountered = el => {}) {
        walk(this.el.rawNode(), (node) => {
            const el = new DOMElement(node)

            // Skip the root component element.
            if (el.isSameNode(this.el)) { callback(el); return; }

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
        if (! this.modelDebounceCallbacks) this.modelDebounceCallbacks = []

        // This is a "null" callback. Each wire:model will resister one of these upon initialization.
        let callbackRegister = { callback: () => {} }
        this.modelDebounceCallbacks.push(callbackRegister)

        // This is a normal "timeout" for a debounce function.
        var timeout

        return (e) => {
            clearTimeout(timeout)

            timeout = setTimeout(() => {
                callback(e)
                timeout = undefined

                // Because we just called the callback, let's return the
                // callback register to it's normal "null" state.
                callbackRegister.callback = () => {}
            }, time)

            // Register the current callback in the register as a kind-of "escape-hatch".
            callbackRegister.callback = () => { clearTimeout(timeout); callback(e); }
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

    upload(name, file, finishCallback = () => {}, errorCallback = () => {}, progressCallback = () => {}) {
        this.uploadManager.upload(name, file, finishCallback, errorCallback, progressCallback)
    }

    uploadMultiple(name, files, finishCallback = () => {}, errorCallback = () => {}, progressCallback = () => {}) {
        this.uploadManager.uploadMultiple(name, files, finishCallback, errorCallback, progressCallback)
    }

    removeUpload(name, tmpFilename, finishCallback = () => {}, errorCallback = () => {}) {
        this.uploadManager.removeUpload(name, tmpFilename, finishCallback, errorCallback)
    }
}
