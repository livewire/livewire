import domWalker from './domWalker.js'
import { shouldUpdateInputElementGivenItHasBeenUpdatedViaSync, transitionElementIn, transitionElementOut, isComponentRootEl, getAttribute, elByAttributeAndValue, elsByAttributeAndValue, preserveActiveElement } from './domHelpers'
const prefix = require('./prefix.js')()
import morphdom from './morphdom/index.js'
import store from './store'

export default class Component {
    constructor(el, nodeInitializer, parent) {
        this.nodeInitializer = nodeInitializer
        this.parent = parent
        this.id = getAttribute(el, 'root-id')
        this.serialized = getAttribute(el, 'root-serialized')
        this.loadingElsByTargetRef = {}
        this.syncQueue = {}
    }

    attachListenersAndAddChildComponents() {
        domWalker.walk(this.el, (node) => {
            if (this.el.isSameNode(node)) {
                return
            }

            if (isComponentRootEl(node)) {
                this.addChildComponent(node)
            }

            this.nodeInitializer.initialize(node);
        })
    }

    get el() {
        // I made this a getter, so that we aren't ever getting a stale DOM element.
        // If it's too slow, we can re-evaluate it.
        return elByAttributeAndValue('root-id', this.id)
    }

    addChildComponent(el) {
        const component = new Component(el, this.nodeInitializer, this)

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

    queueSyncInput(model, value) {
        this.syncQueue[model] = value
    }

    clearSyncQueue() {
        this.syncQueue = {}
    }

    setLoading(refName) {
        (this.loadingElsByTargetRef[refName] || []).forEach(el => {
            el.classList.remove('hidden')
        })
    }

    unsetLoading(refName) {
        (this.loadingElsByTargetRef[refName] || []).forEach(el => {
            el.classList.add('hidden')
        })
    }

    handleMorph(dom, dirtyInputs) {
        morphdom(this.el, dom, {
            onBeforeNodeAdded: node => {
                if (typeof node.hasAttribute !== 'function') return

                transitionElementIn(node)
            },

            onBeforeNodeDiscarded: node => {
                if (typeof node.hasAttribute !== 'function') return

                return transitionElementOut(node)
            },

            onBeforeElChildrenUpdated: from => {
                if (isComponentRootEl(from) && ! from.isSameNode(this.el)) {
                    return false
                }
            },

            onBeforeElUpdated: from => {
                if (isComponentRootEl(from) && ! from.isSameNode(this.el)) {
                    return false
                }

                return shouldUpdateInputElementGivenItHasBeenUpdatedViaSync(from, dirtyInputs)
            },

            onNodeAdded: (node) => {
                if (typeof node.hasAttribute !== 'function') return

                if (isComponentRootEl(node)) {
                    this.addChildComponent(node)
                }

                this.nodeInitializer.initialize(node)
            },
        });
    }
}
