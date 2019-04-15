import Message from '../message'
import { debounce } from 'lodash'
import { addMixin, tap } from '../util'
import morphdom from '../dom/morphdom'
import TreeWalker from '../dom/tree_walker'
import LivewireElement from '../dom/element'
import handleLoadingDirectives from './handle_loading_directives'

class Component {
    constructor(el, nodeInitializer, connection, parent) {
        this.serialized = JSON.parse(el.getAttribute('serialized'))
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
        this.connection.sendMessage(new Message(
            this,
            this.actionQueue,
            this.syncQueue,
        ))

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

    receiveMessage(message) {
        // Note: I'm sure there is an abstraction called "MessageResponse" that makes sense.
        // Let's just keep an eye on this for now. Sorry for the LoD violation.
        this.serialized = JSON.parse(message.response.serialized)

        // This means "$this->redirect()" was called in the component. let's just bail and redirect.
        if (message.response.redirectTo) {
            window.location.href = message.response.redirectTo
            return
        }

        this.replaceDom(message.response.dom, message.response.dirtyInputs)

        this.unsetLoading(message.loadingEls)
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

        // Go through and add any "value" attributes to "wire:model" bound input elements,
        // if they aren't already in the dom.
        LivewireElement.allModelElementsInside(tempDom).forEach(el => {
            const modelValue = el.directives.get('model').value

            // @todo - remove this el.el
            if (! el.el.hasAttribute('value') && this.serialized.properties[modelValue]) {
                el.el.setAttribute('value', this.serialized.properties[modelValue])
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
