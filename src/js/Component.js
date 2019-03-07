import domWalker from './DomWalker'
import morphdom from './morphdom/index'
import store from './Store'
import NodeInitializer from './NodeInitializer'
import LivewireElement from './LivewireElement'

export default class Component {
    constructor(el, connection, parent) {
        this.connection = connection
        this.nodeInitializer = new NodeInitializer(connection)
        this.parent = parent
        this.id = el.getAttribute('id')
        this.serialized = el.getAttribute('serialized')
        this.syncQueue = {}
    }

    attachListenersAndAddChildComponents() {
        domWalker.walk(this.el, (node) => {
            if (typeof node.hasAttribute !== 'function') return

            const el = new LivewireElement(node)

            if (el.isSameNode(this.el)) {
                return
            }

            if (el.isComponentRootEl()) {
                this.addChildComponent(el)
            }

            this.nodeInitializer.initialize(el);
        })
    }

    get el() {
        // I made this a getter, so that we aren't ever getting a stale DOM element.
        // If it's too slow, we can re-evaluate it.
        return elByAttributeAndValue('id', this.id)
    }

    addChildComponent(el) {
        const component = new Component(el, this.connection, this)

        store.componentsById[component.id] = component
    }

    replace(dom, dirtyInputs, serialized) {
        this.serialized = serialized;

        // Prevent morphdom from moving an input element and it losing it's focus.
        preserveActiveElement(() => {
            this.handleMorph(dom.trim(), dirtyInputs)
        })
    }

    addLoadingEl(el, ref) {
        if (this.loadingElsByTargetRef[ref]) {
            this.loadingElsByTargetRef[ref].push(el)
        } else {
            this.loadingElsByTargetRef[ref] = [el]
        }
    }

    queueModelSync(model, value) {
        this.syncQueue[model] = value
    }

    clearSyncQueue() {
        this.syncQueue = {}
    }

    setLoading(refName) {
        elsByAttributeAndValue('loading', refName, this.el).forEach(el => {
            el.classList.remove('hidden')
        })
    }

    unsetLoading(refName) {
        elsByAttributeAndValue('loading', refName, this.el).forEach(el => {
            el.classList.add('hidden')
        })
    }

    handleMorph(dom, dirtyInputs) {
        morphdom(this.el, dom, {
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
                    this.addChildComponent(el)
                }

                this.nodeInitializer.initialize(el)
            },
        });
    }
}
