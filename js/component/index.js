import Message from '@/Message'
import PrefetchMessage from '@/PrefetchMessage'
import { debounce, walk } from '@/util'
import morphdom from '@/dom/morphdom'
import DOM from '@/dom/dom'
import DOMElement from '@/dom/dom_element'
import nodeInitializer from '@/node_initializer'
import store from '@/Store'
import PrefetchManager from './PrefetchManager'
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

        store.callHook('componentInitialized', this)

        this.initialize()

        this.registerEchoListeners()

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
        return this.data[name]
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

                if (event.ancestorsOnly) {
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
                return node.hasAttribute(`${DOM.prefix}:key`)
                    ? node.getAttribute(`${DOM.prefix}:key`)
                    // If no "key", then first check for "wire:id", then "wire:model", then "id"
                    : (node.hasAttribute(`${DOM.prefix}:id`)
                        ? node.getAttribute(`${DOM.prefix}:id`)
                        : node.id)
            },

            onBeforeNodeAdded: node => {
                return (new DOMElement(node)).transitionElementIn()
            },

            onBeforeNodeDiscarded: node => {
                const el = new DOMElement(node)

                return el.transitionElementOut(nodeDiscarded => {
                    store.callHook('elementRemoved', el, this)
                })
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
            },

            onElUpdated: (node) => {
                this.morphChanges.changed.push(node)
            },

            onNodeAdded: (node) => {
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

            callback(el)
        })
    }

    registerEchoListeners() {
        if (Array.isArray(this.events)) {
            this.events.forEach(event => {
                if (event.startsWith('echo')) {
                    if (typeof Echo === 'undefined') {
                        console.warn('Laravel Echo cannot be found')
                        return
                    }

                    let event_parts = event.split(/(echo:|echo-)|:|,/)

                    if (event_parts[1] == 'echo:') {
                        event_parts.splice(2,0,'channel',undefined)
                    }

                    if (event_parts[2] == 'notification') {
                        event_parts.push(undefined, undefined)
                    }

                    let [s1, signature, channel_type, s2, channel, s3, event_name] = event_parts

                    if (['channel','private'].includes(channel_type)) {
                        Echo[channel_type](channel).listen(event_name, (e) => {
                            store.emit(event, e)
                        })
                    } else if (channel_type == 'presence') {
                        Echo.join(channel)[event_name]((e) => {
                            store.emit(event, e)
                        })
                    } else if (channel_type == 'notification') {
                        Echo.private(channel).notification((notification) => {
                            store.emit(event, notification)
                        })
                    } else{
                        console.warn('Echo channel type not yet supported')
                    }
                }
            })
        }
    }

    modelSyncDebounce(callback, time) {
        return (e) => {
            clearTimeout(this.modelTimeout)

            this.modelTimeoutCallback = () => { callback(e) }
            this.modelTimeout = setTimeout(() => {
                callback(e)
                this.modelTimeout = null
                this.modelTimeoutCallback = null
            }, time)
        }
    }

    callAfterModelDebounce(callback) {
        // This is to protect against the following scenario:
        // A user is typing into a debounced input, and hits the enter key.
        // If the enter key submits a form or something, the submission
        // will happen BEFORE the model input finishes syncing because
        // of the debounce. This makes sure to clear anything in the debounce queue.
        if (this.modelTimeout) {
            clearTimeout(this.modelTimeout)
            this.modelTimeoutCallback()
            this.modelTimeout = null
            this.modelTimeoutCallback = null
        }

        callback()
    }

    addListenerForTeardown(teardownCallback) {
        this.tearDownCallbacks.push(teardownCallback)
    }

    tearDown() {
        this.tearDownCallbacks.forEach(callback => callback())
    }
}
