import Message from '../message'
import { debounce } from 'lodash'
import { addMixin, tap } from '../util'
import morphdom from '../dom/morphdom'
import TreeWalker from '../dom/tree_walker'
import LivewireElement from '../dom/element'
import handleLoadingDirectives from './handle_loading_directives'

class Component {
    constructor(el, nodeInitializer, connection, parent) {
        this.currentMessage = null
        this.serialized = JSON.parse(el.getAttribute('serialized'))
        this.events = JSON.parse(el.getAttribute('listening-for'))
        this.id = el.getAttribute('id')
        this.nodeInitializer = nodeInitializer
        this.connection = connection
        this.syncQueue = {}
        this.actionQueue = []

        this.initialize(el)
    }

    initialize(el) {
        const walker = new TreeWalker

        walker.walk(el.rawNode(), (node) => {
            if (typeof node.hasAttribute !== 'function') return
            if (node.isSameNode(this.el.rawNode())) return

            const el = new LivewireElement(node)

            this.nodeInitializer.initialize(el, this)
        })
    }

    get el() {
        // I made this a getter, so that we aren't ever getting a stale DOM element.
        // If it's too slow, we can re-evaluate it.
        return LivewireElement.byAttributeAndValue('id', this.id)
    }

    addAction(action) {
        this.actionQueue.push(action)

        // This debounce is here in-case two events fire at the "same" time:
        // For example: if you are listening for a click on element A,
        // and a "blur" on element B. If element B has focus, and then,
        // you click on element A, the blur event will fire before the "click"
        // event. This debounce captures them both in the actionsQueue and sends
        // them off at the same time.
        // Note: currently, it's set to 5ms, that might not be the right amount, we'll see.
        debounce(this.fireMessage, 5).apply(this)
    }

    fireMessage() {
        if (this.currentMessage) return

        this.currentMessage = new Message(
            this,
            this.actionQueue,
            this.syncQueue,
        );

        this.connection.sendMessage(this.currentMessage)

        this.clearSyncQueue()
        this.clearActionQueue()
    }

    queueSyncInput(model, value) {
        this.syncQueue[model] = value
    }

    clearSyncQueue() {
        this.syncQueue = {}
    }

    clearActionQueue() {
        this.actionQueue = []
    }

    receiveMessage(payload) {
        this.currentMessage.storeResponse(payload)

        // Note: I'm sure there is an abstraction called "MessageResponse" that makes sense.
        // Let's just keep an eye on this for now. Sorry for the LoD violation.
        this.serialized = this.currentMessage.response.serialized

        // This means "$this->redirect()" was called in the component. let's just bail and redirect.
        if (this.currentMessage.response.redirectTo) {
            window.location.href = this.currentMessage.response.redirectTo
            return
        }

        this.replaceDom(this.currentMessage.response.dom, this.currentMessage.response.dirtyInputs)

        this.unsetLoading(this.currentMessage.loadingEls)

        this.currentMessage = null

        if (payload.eventQueue && payload.eventQueue.length > 0) {
            payload.eventQueue.forEach(event => {
                // @todo - stop depending on window.livewire
                window.livewire.emit(event.event, ...event.params)
            })
        }
    }

    replaceDom(rawDom, dirtyInputs) {
        // Prevent morphdom from moving an input element and it losing it's focus.
        LivewireElement.preserveActiveElement(() => {
            this.handleMorph(this.addValueAttributesToModelNodes(rawDom.trim()), dirtyInputs)
        })
    }

    addValueAttributesToModelNodes(inputDom)
    {
        const tempDom = tap(document.createElement('div'), el => { el.innerHTML = inputDom })

        // I need the "self" for the later eval().
        const self = this

        // Go through and add any "value" attributes to "wire:model" bound input elements,
        // if they aren't already in the dom.
        LivewireElement.allModelElementsInside(tempDom).forEach(el => {
            const modelValue = el.directives.get('model').value

            const modelValueWithArraySyntaxForNumericKeys = modelValue.replace(/\.([0-9]+)/, (match, num) => { return `[${num}]` })

            // @todo - remove this el.el
            if (! el.el.hasAttribute('value') && eval('self.serialized.properties.'+modelValueWithArraySyntaxForNumericKeys)) {
                el.el.setAttribute('value', eval('self.serialized.properties.'+modelValueWithArraySyntaxForNumericKeys))
            }
        })

        return tempDom.innerHTML
    }

    handleMorph(dom, dirtyInputs) {
        morphdom(this.el.rawNode(), dom, {
            getNodeKey: node => {
                // This allows the tracking of elements by the "key" attribute, like in VueJs.
                return node.hasAttribute('key')
                    ? node.getAttribute('key')
                    : node.id
            },

            onBeforeNodeAdded: node => {
                return (new LivewireElement(node)).transitionElementIn()
            },

            onBeforeNodeDiscarded: node => {
                return (new LivewireElement(node)).transitionElementOut(nodeDiscarded => {
                    this.removeLoadingEl(nodeDiscarded)
                })
            },

            onBeforeElChildrenUpdated: node => {
                //
            },

            onBeforeElUpdated: (from, to) => {
                const fromEl = new LivewireElement(from)
                const toEl = new LivewireElement(to)

                toEl.preserveValueAttributeIfNotDirty(fromEl, dirtyInputs)
            },

            onElUpdated: (node) => {
                //
            },

            onNodeDiscarded: node => {
                // Elements with loading directives are stored, release this
                // element from storage because it no longer exists on the DOM.
                this.removeLoadingEl(node)
            },

            onNodeAdded: (node) => {
                this.nodeInitializer.initialize(new LivewireElement(node), this)
            },
        });
    }
}

addMixin(Component, handleLoadingDirectives)

export default Component
