import store from '@/Store'
import Message from '@/Message';

export default function () {

    let initializedPath = false

    // This is to prevent exponentially increasing the size of our state on page refresh.
    if (window.history.state) window.history.state.livewire = (new LivewireState).toStateArray();

    store.registerHook('component.initialized', component => {
        if (! component.effects.path) return

        // We are using setTimeout() to make sure all the components on the page have
        // loaded before we store anything in the history state (because the position
        // of a component on a page matters for generating its state signature).
        setTimeout(() => {
            let state = generateNewState(component, generateInitialFauxResponse(component))

            let url = initializedPath ? undefined : component.effects.path

            store.callHook('beforeReplaceState', state, url, component)

            history.replaceState(state, '', onlyChangeThePathAndQueryString(url))

            initializedPath = true
        })
    })

    store.registerHook('message.processed', (message, component) => {
        // Preventing a circular dependancy.
        if (message.replaying) return

        let { response } = message

        let effects = response.effects || {}

        if ('path' in effects && effects.path !== window.location.href) {
            let state = generateNewState(component, response)

            store.callHook('beforePushState', state, effects.path, component)

            history.pushState(state, '', onlyChangeThePathAndQueryString(effects.path))
        }
    })

    window.addEventListener('popstate', event => {
        if (! (event.state && event.state.livewire)) return

        (new LivewireState(event.state.livewire)).replayResponses((response, component) => {
            let message = new Message(component, [])

            message.storeResponse(response)

            message.replaying = true

            component.handleResponse(message)
        })
    })

    function generateNewState(component, response) {
        let state = history.state && history.state.livewire
            ? new LivewireState([...history.state.livewire])
            : new LivewireState

        state.storeResponse(response, component)

        return { livewire: state.toStateArray() }
    }

    function generateInitialFauxResponse(component) {
        let { serverMemo, effects, el } = component

        return {
            serverMemo,
            effects: { ...effects, html: el.outerHTML }
        }
    }

    function onlyChangeThePathAndQueryString(url) {
        if (! url) return

        let destination = new URL(url)

        let afterOrigin = destination.href.replace(destination.origin, '')

        return window.location.origin + afterOrigin + window.location.hash
    }

    store.registerHook('element.updating', (from, to, component) => {
        // It looks like the element we are about to update is the root
        // element of the component. Let's store this knowledge to
        // reference after update in the "element.updated" hook.
        if (from.getAttribute('wire:id') === component.id) {
            component.lastKnownDomId = component.id
        }
    })

    store.registerHook('element.updated', (node, component) => {
        // If the element that was just updated was the root DOM element.
        if (component.lastKnownDomId) {
            // Let's check and see if the wire:id was the thing that changed.
            if (node.getAttribute('wire:id') !== component.lastKnownDomId) {
                // If so, we need to change this ID globally everwhere it's referenced.
                store.changeComponentId(component, node.getAttribute('wire:id'))
            }

            // Either way, we'll unset this for the next update.
            delete component.lastKnownDomId
        }

        // We have to update the component ID because we are replaying responses
        // from similar components but with completely different IDs. If didn't
        // update the component ID, the checksums would fail.
    })
}

class LivewireState
{
    constructor(stateArray = []) { this.items = stateArray }

    toStateArray() { return this.items }

    pushItemInProperOrder(signature, storageKey, component) {
        let targetItem = { signature, storageKey }

        // First, we'll check if this signature already has an entry, if so, replace it.
        let existingIndex = this.items.findIndex(item => item.signature === signature)

        if (existingIndex !== -1) return this.items[existingIndex] = targetItem

        // If it doesn't already exist, we'll add it, but we MUST first see if any of its
        // parents components have entries, and insert it immediately before them.
        // This way, when we replay responses, we will always start with the most
        // inward components and go outwards.

        let closestParentId = store.getClosestParentId(component.id, this.componentIdsWithStoredResponses())

        if (! closestParentId) return this.items.unshift(targetItem)

        let closestParentIndex = this.items.findIndex(item => {
            let { originalComponentId } = this.parseSignature(item.signature)

            if (originalComponentId === closestParentId) return true
        })

        this.items.splice(closestParentIndex, 0, targetItem);
    }

    storeResponse(response, component) {
        // Add ALL properties as "dirty" so that when the back button is pressed,
        // they ALL are forced to refresh on the page (even if the HTML didn't change).
        response.effects.dirty = Object.keys(response.serverMemo.data)

        let storageKey = this.storeInSession(response)

        let signature = this.getComponentNameBasedSignature(component)

        this.pushItemInProperOrder(signature, storageKey, component)
    }

    replayResponses(callback) {
        this.items.forEach(({ signature, storageKey }) => {
            let component = this.findComponentBySignature(signature)

            if (! component) return

            let response = this.getFromSession(storageKey)

            if (! response) return console.warn(`Livewire: sessionStorage key not found: ${storageKey}`)

            callback(response, component)
        })
    }

    storeInSession(value) {
        let key = Math.random().toString(36).substring(2)

        sessionStorage.setItem(key, JSON.stringify(Object.entries(value)))

        return key
    }

    getFromSession(key) {
        return Object.fromEntries(JSON.parse(sessionStorage.getItem(key)))
    }

    // We can't just store component reponses by their id because
    // ids change on every refresh, so history state won't have
    // a component to apply it's changes to. Instead we must
    // generate a unique id based on the components name
    // and it's relative position amongst others with
    // the same name that are loaded on the page.
    getComponentNameBasedSignature(component) {
        let componentName = component.fingerprint.name
        let sameNamedComponents = store.getComponentsByName(componentName)
        let componentIndex = sameNamedComponents.indexOf(component)

        return `${component.id}:${componentName}:${componentIndex}`
    }

    findComponentBySignature(signature) {
        let { componentName, componentIndex } = this.parseSignature(signature)

        let sameNamedComponents = store.getComponentsByName(componentName)

        // If we found the component in the proper place, return it,
        // otherwise return the first one.
        return sameNamedComponents[componentIndex] || sameNamedComponents[0] || console.warn(`Livewire: couldn't find component on page: ${componentName}`)
    }

    parseSignature(signature) {
        let [originalComponentId, componentName, componentIndex] = signature.split(':')

        return { originalComponentId, componentName, componentIndex }
    }

    componentIdsWithStoredResponses() {
        return this.items.map(({ signature }) => {
            let { originalComponentId } = this.parseSignature(signature)

            return originalComponentId
        })
    }
}
