import store from '@/Store'
import Message from '@/Message';

export default function () {

    let initializedPath = false

    let componentIdsThatAreWritingToHistoryState = new Set

    LivewireStateManager.clearState()

    store.registerHook('component.initialized', component => {
        if (! component.effects.path) return

        // We are using setTimeout() to make sure all the components on the page have
        // loaded before we store anything in the history state (because the position
        // of a component on a page matters for generating its state signature).
        setTimeout(() => {
            let url = onlyChangeThePathAndQueryString(initializedPath ? undefined : component.effects.path)

            // Generate faux response.
            let response = {
                serverMemo: component.serverMemo,
                effects: component.effects,
            }

            normalizeResponse(response, component)

            LivewireStateManager.replaceState(url, response, component)

            componentIdsThatAreWritingToHistoryState.add(component.id)

            initializedPath = true
        })
    })

    store.registerHook('message.processed', (message, component) => {
        // Preventing a circular dependancy.
        if (message.replaying) return

        let { response } = message

        let effects = response.effects || {}

        normalizeResponse(response, component)

        if ('path' in effects && effects.path !== window.location.href) {
            let url = onlyChangeThePathAndQueryString(effects.path)

            LivewireStateManager.pushState(url, response, component)

            componentIdsThatAreWritingToHistoryState.add(component.id)
        } else {
            // If the current component has changed it's state, but hasn't written
            // anything new to the URL, we still need to update it's data in the
            // history state so that when a back button is hit, it is caught
            // up to the most recent known data state.
            if (componentIdsThatAreWritingToHistoryState.has(component.id)) {
                LivewireStateManager.replaceState(window.location.href, response, component)
            }
        }
    })

    window.addEventListener('popstate', event => {
        if (LivewireStateManager.missingState(event)) return

        LivewireStateManager.replayResponses(event, (response, component) => {
            let message = new Message(component, [])

            message.storeResponse(response)

            message.replaying = true

            component.handleResponse(message)
        })
    })

    function normalizeResponse(response, component) {
        // Add ALL properties as "dirty" so that when the back button is pressed,
        // they ALL are forced to refresh on the page (even if the HTML didn't change).
        response.effects.dirty = Object.keys(response.serverMemo.data)

        // Sometimes Livewire doesn't return html from the server to save on bandwidth.
        // So we need to set the HTML no matter what.
        response.effects.html = component.lastFreshHtml
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

let LivewireStateManager = {
    replaceState(url, response, component) {
        this.updateState('replaceState', url, response, component)
    },

    pushState(url, response, component) {
        this.updateState('pushState', url, response, component)
    },

    updateState(method, url, response, component) {
        let state = this.currentState()

        state.storeResponse(response, component)

        let stateArray = state.toStateArray()

        // Copy over existing history state if it's an object, so we don't overwrite it.
        let fullstateObject = Object.assign(history.state || {}, { livewire: stateArray })

        let capitalize = subject => subject.charAt(0).toUpperCase() + subject.slice(1)

        store.callHook('before'+capitalize(method), fullstateObject, url, component)

        try {
            history[method](fullstateObject, '', url)
        } catch (error) {
            // Firefox has a 160kb limit to history state entries.
            // If that limit is reached, we'll instead put it in
            // sessionStorage and store a reference to it.
            if (error.name === 'NS_ERROR_ILLEGAL_VALUE') {
                let key = this.storeInSession(stateArray)

                fullstateObject.livewire = key

                history[method](fullstateObject, '', url)
            }
        }
    },

    replayResponses(event, callback) {
        if (! event.state.livewire) return

        let state = typeof event.state.livewire === 'string'
            ? new LivewireState(this.getFromSession(event.state.livewire))
            : new LivewireState(event.state.livewire)

        state.replayResponses(callback)
    },

    currentState() {
        if (! history.state) return new LivewireState
        if (! history.state.livewire) return new LivewireState

        let state = typeof history.state.livewire === 'string'
            ? new LivewireState(this.getFromSession(history.state.livewire))
            : new LivewireState(history.state.livewire)

        return state
    },

    missingState(event) {
        return ! (event.state && event.state.livewire)
    },

    clearState() {
        // This is to prevent exponentially increasing the size of our state on page refresh.
        if (window.history.state) window.history.state.livewire = (new LivewireState).toStateArray();
    },

    storeInSession(value) {
        let key = 'livewire:'+(new Date).getTime()

        let stringifiedValue = JSON.stringify(value)

        this.tryToStoreInSession(key, stringifiedValue)

        return key
    },

    tryToStoreInSession(key, value) {
        // sessionStorage has a max storage limit (usally 5MB).
        // If we meet that limit, we'll start removing entries
        // (oldest first), until there's enough space to store
        // the new one.
        try {
            sessionStorage.setItem(key, value)
        } catch (error) {
            // 22 is Chrome, 1-14 is other browsers.
            if (! [22, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14].includes(error.code)) return

            let oldestTimestamp = Object.keys(sessionStorage)
                .map(key => Number(key.replace('livewire:', '')))
                .sort()
                .shift()

            if (! oldestTimestamp) return

            sessionStorage.removeItem('livewire:'+oldestTimestamp)

            this.tryToStoreInSession(key, value)
        }
    },

    getFromSession(key) {
        let item = sessionStorage.getItem(key)

        if (! item) return

        return JSON.parse(item)
    },
}

class LivewireState
{
    constructor(stateArray = []) { this.items = stateArray }

    toStateArray() { return this.items }

    pushItemInProperOrder(signature, response, component) {
        let targetItem = { signature, response }

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
        let signature = this.getComponentNameBasedSignature(component)

        this.pushItemInProperOrder(signature, response, component)
    }

    replayResponses(callback) {
        this.items.forEach(({ signature, response }) => {
            let component = this.findComponentBySignature(signature)

            if (! component) return

            callback(response, component)
        })
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
