import store from '../store'
import Message from '../message'
import { debounce } from 'lodash'
import { addMixin } from '../util'
import morphdom from '../dom/morphdom'
import domWalker from '../dom/tree_walker'
import LivewireElement from '../dom/element'
import handleLoadingDirectives from './handle_loading_directives'

class Component {
    constructor(el, nodeInitializer, connection, parent) {
        this.serialized = el.getAttribute('serialized')
        this.id = el.getAttribute('id')
        this.nodeInitializer = nodeInitializer
        this.connection = connection
        this.parent = parent
        this.syncQueue = {}
        this.actionQueue = []
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

        this.clearActionQueue() && this.clearSyncQueue()
    }

    queueSyncInput(model, value) {
        this.syncQueue[model] = value
    }

    clearSyncQueue() {
        this.syncQueue = {}
    }

    clearActionQueue() {
        this.syncQueue = []
    }

    attachListenersAndProcessChildComponents(callback) {
        // This starts as the root component, but will become children as they are encountered.
        let componentBeingWalked = this

        domWalker.walk(this.el.rawNode(), (node) => {
            if (typeof node.hasAttribute !== 'function') return
            if (node.isSameNode(this.el.rawNode())) return

            const el = new LivewireElement(node)

            if (el.isComponentRootEl()) {
                componentBeingWalked = callback.apply(this, [el])
            }

            this.nodeInitializer.initialize(el, componentBeingWalked);
        })
    }

    receiveMessage(message, eventCallback) {
        // Note: I'm sure there is an abstraction called "MessageResponse" that makes sense.
        // Let's just keep an eye on this for now. Sorry for the LoD violation.
        this.serialized = message.response.serialized

        // This means "$this->redirect()" was called in the component. let's just bail and redirect.
        if (message.response.redirectTo) {
            window.location.href = message.response.redirectTo
            return
        }

        this.replaceDom(message.response.dom, message.response.dirtyInputs)

        this.unsetLoading(message.loadingEls)

        // This means "$this->emit()" was called in the component.
        message.response.emitEvent && eventCallback(message.response.emitEvent)
    }

    replaceDom(rawDom, dirtyInputs) {
        // Prevent morphdom from moving an input element and it losing it's focus.
        LivewireElement.preserveActiveElement(() => {
            this.handleMorph(rawDom.trim(), dirtyInputs)
        })
    }

    handleMorph(dom, dirtyInputs) {
        let currentComponent = this

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
                const el = new LivewireElement(node)

                // Don't update the DOM of child components. They will update themselves.
                if (el.isComponentRootEl() && ! el.isSameNode(this.el)) {
                    return false
                }
            },

            onBeforeElUpdated: node => {
                const el = new LivewireElement(node)

                // Don't update the child component DOM root element.
                if (el.isComponentRootEl() && ! el.isSameNode(this.el)) {
                    return false
                }

                return el.shouldUpdateInputElementGivenItHasBeenUpdatedViaSync(dirtyInputs)
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
                const el = new LivewireElement(node)

                if (el.isComponentRootEl()) {
                    // We've encountered a new child component, let's register and initialize it.
                    currentComponent = store.addComponent(new Component(el, this.nodeInitializer, this.connection, this))
                }

                this.nodeInitializer.initialize(el, currentComponent)
            },
        });
    }
}

addMixin(Component, handleLoadingDirectives)

export default Component
