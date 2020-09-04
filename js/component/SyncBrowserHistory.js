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

    let cached = {}

    store.registerHook('component.initialized', component => {
        withDebug(`Initialized ${component.name}`, () => {
            let state = generateNewState(component, generateFauxResponse(component))
            let url = 'path' in component.effects ? component.effects.path : undefined

            console.log('State', state.livewire)
            console.log('URL', url)

            history.replaceState(state, '', url)
        })
    })

    store.registerHook('message.receiving', (message, component) => {
        cached = JSON.parse(JSON.stringify(component.serverMemo));
    });

    store.registerHook('message.processed', (message, component) => {
        withDebug(`Message processed for ${component.name}`, () => {
            let { replaying, response } = message
            if (replaying) {
                console.log('Replaying - skipping pushState')
                return
            }

            let { effects } = response

            if ('path' in effects && effects.path !== window.location.href) {

                let state = generateNewState(component, response, cached)
                cached = {}

                console.warn('Pushing new state')
                console.log('State', state.livewire);
                console.log('Component Server Memo', component.serverMemo);
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

    function generateNewState(component, response, cache = {}) {
        let state = (history.state && history.state.livewire) ? { ...history.state.livewire } : {}

        let data = {
            ...response,
            serverMemo: {
                ...cache,
                ...response.serverMemo,
                data: {
                    ...cache.data,
                    ...response.serverMemo.data,
                }
            }
        }

        state[component.id] = JSON.parse(JSON.stringify(data))

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
