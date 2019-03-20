import domWalker from '../DomWalker'
import { debounce } from 'lodash'
import store from '../Store'
import Message from '../Message'
import morphdom from '../morphdom/index'
import LivewireElement from '../LivewireElement'
import handleLoadingDirectives from './handle_loading_directives'

class Component {
    constructor(el, nodeInitializer, connection, parent) {
        this.serialized = el.getAttribute('serialized')
        this.nodeInitializer = nodeInitializer
        this.connection = connection
        this.id = el.getAttribute('id')
        this.parent = parent
        this.syncQueue = {}
        this.actionQueue = []
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

        this.actionQueue = []
        this.syncQueue = {}
    }

    attachListenersAndProcessChildComponents(callback) {
        // This starts as the root component, but will become children as they are encountered.
        var currentComponent = this

        domWalker.walk(this.el.rawNode(), (node) => {
            if (typeof node.hasAttribute !== 'function') return
            if (node.isSameNode(this.el.rawNode())) return

            const el = new LivewireElement(node)

            if (el.isComponentRootEl()) {
                currentComponent = callback.apply(this, [el])
            }

            this.nodeInitializer.initialize(el, currentComponent);
        })
    }

    get el() {
        // I made this a getter, so that we aren't ever getting a stale DOM element.
        // If it's too slow, we can re-evaluate it.
        return LivewireElement.byAttributeAndValue('id', this.id)
    }

    receiveMessage(message, eventCallback) {
        // Note: I'm sure there is an abstraction called "MessageResponse" that makes sense.
        // Let's just keep an eye on this for now. Sorry for the LoD violation.
        this.serialized = message.response.serialized;

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

    queueSyncInput(model, value) {
        this.syncQueue[model] = value
    }

    clearSyncQueue() {
        this.syncQueue = {}
    }

    handleMorph(dom, dirtyInputs) {
        var currentComponent = this
        morphdom(this.el.rawNode(), dom, {
            onBeforeNodeAdded: node => {
                if (typeof node.hasAttribute !== 'function') return

                const el = new LivewireElement(node)

                el.transitionElementIn()
            },

            onBeforeNodeDiscarded: node => {
                if (typeof node.hasAttribute !== 'function') return

                const el = new LivewireElement(node)

                return el.transitionElementOut()
            },

            onBeforeElChildrenUpdated: node => {
                if (typeof node.hasAttribute !== 'function') return

                const el = new LivewireElement(node)

                if (el.isComponentRootEl() && ! el.isSameNode(this.el)) {
                    return false
                }
            },

            onBeforeElUpdated: node => {
                if (typeof node.hasAttribute !== 'function') return

                const el = new LivewireElement(node)

                if (el.isComponentRootEl() && ! el.isSameNode(this.el)) {
                    return false
                }

                return el.shouldUpdateInputElementGivenItHasBeenUpdatedViaSync(dirtyInputs)
            },

            onElUpdated: (node) => {
                if (typeof node.hasAttribute !== 'function') return
            },

            onNodeDiscarded: node => {
                if (typeof node.hasAttribute !== 'function') return

                this.removeLoadingEl(node)
            },

            onNodeAdded: (node) => {
                if (typeof node.hasAttribute !== 'function') return

                const el = new LivewireElement(node)

                if (el.isComponentRootEl()) {
                    currentComponent = store.addComponent(new Component(el, this.nodeInitializer, this.connection, this))
                }

                this.nodeInitializer.initialize(el, currentComponent)
            },
        });
    }
}

Object.assign(Component.prototype, handleLoadingDirectives)

export default Component
