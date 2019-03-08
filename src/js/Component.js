import domWalker from './DomWalker'
import store from './Store'
import morphdom from './morphdom/index'
import LivewireElement from './LivewireElement'

export default class Component {
    constructor(el, nodeInitializer, parent) {
        this.serialized = el.getAttribute('serialized')
        this.nodeInitializer = nodeInitializer
        this.loadingElsWithNoTarget = []
        this.loadingElsByTargetRef = {}
        this.id = el.getAttribute('id')
        this.parent = parent
        this.syncQueue = {}
    }

    attachListenersAndProcessChildComponents(callback) {
        domWalker.walk(this.el.rawNode(), (node) => {
            if (typeof node.hasAttribute !== 'function') return
            if (node.isSameNode(this.el.rawNode())) return

            const el = new LivewireElement(node)

            this.nodeInitializer.initialize(el);

            if (el.isComponentRootEl()) {
                callback(el)
            }
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
            window.location.href = redirectTo
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

    queueModelSync(model, value) {
        this.syncQueue[model] = value
    }

    clearSyncQueue() {
        this.syncQueue = {}
    }

    addLoadingEl(el, value, ref, remove) {
        if (ref) {
            // There's gotta be a more elegant way...
            if (this.loadingElsByTargetRef[ref].length) {
                this.loadingElsByTargetRef[ref].push({el, value, remove})
            } else {
                this.loadingElsByTargetRef[ref] = [{el, value, remove}]
            }
        } else {
            this.loadingElsWithNoTarget.push({el, value, remove})
        }
    }

    setLoading(refName) {
        const allEls = this.loadingElsWithNoTarget.concat(
            this.loadingElsByTargetRef[refName] || []
        )

        allEls.forEach(el => {
            if (el.remove) {
                el.el.classList.remove(el.value)
            } else {
                el.el.classList.add(el.value)
            }
        })

        return allEls
    }

    unsetLoading(loadingEls) {
        loadingEls.forEach(el => {
            if (el.remove) {
                el.el.classList.add(el.value)
            } else {
                el.el.classList.remove(el.value)
            }
        })
    }

    handleMorph(dom, dirtyInputs) {
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

            onNodeAdded: (node) => {
                if (typeof node.hasAttribute !== 'function') return

                const el = new LivewireElement(node)

                if (el.isComponentRootEl()) {
                    store.addComponent(new Component(el, this.nodeInitializer, this))
                }

                this.nodeInitializer.initialize(el)
            },
        });
    }
}
