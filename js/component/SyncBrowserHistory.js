import store from '@/Store'
import qs from '@/util/query-string'
import Message from '@/Message';

function withDebug(group, callback) {
    console.groupCollapsed(group);

    try {
        callback();
    } catch (e) {
        console.error(e);
    }

    console.groupEnd();
}

export default function () {

    store.registerHook('component.initialized', component => {
        withDebug(`Initialized ${component.name}`, () => {
            let state = generateNewState(component, generateFauxResponse(component))
            let url = 'path' in component.effects ? component.effects.path : undefined

            console.log('State', state)
            console.log('URL', url)

            history.replaceState(state, '', url)
        })
    })

    store.registerHook('message.received', (message, component) => {
        withDebug(`Message received for ${component.name}`, () => {
            let { replaying, response } = message
            if (replaying) {
                console.log('Replaying - skipping pushState')
                return
            }

            let { effects } = response

            if ('path' in effects && effects.path !== window.location.href) {

                let state = generateNewState(component, response)

                console.warn('Pushing new state')
                console.log('State', state);
                console.log('Current URL', window.location.href);
                console.log('New URL', effects.path);

                history.pushState(state, '', effects.path)
            } else {
                console.log('No path effect, skipping')
            }
        })
    })

    window.addEventListener('popstate', event => {
        if (!(event && event.state && event.state.livewire)) return

        withDebug('"popstate" event', () => {
            Object.entries(event.state.livewire).forEach(([id, response]) => {
                withDebug(`Component "${id}"`, () => {
                    let component = store.findComponent(id)
                    if (!component) {
                        console.log('Cannot find component - aborting')
                        return
                    }

                    let message = new Message(component, [])
                    message.storeResponse(response)
                    message.replaying = true

                    console.log('About to replay:', message)
                    component.handleResponse(message)

                    console.log('Calling $refresh')
                    component.call('$refresh')
                })
            })
        })
    })

    function generateNewState(component, response) {
        let state = (history.state && history.state.livewire) ? { ...history.state.livewire } : {}

        state[component.id] = JSON.parse(JSON.stringify(response))

        return { turbolinks: {}, livewire: state }
    }

    function generateFauxResponse(component) {
        let { fingerprint, serverMemo, effects, el } = component
        return {
            fingerprint,
            serverMemo,
            effects: { ...effects, html: el.outerHTML }
        }
    }
}
