import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { fireAction } from './index'

describe('Request System', () => {
    beforeEach(() => {
        vi.stubGlobal('livewireScriptConfig', { csrf: 'test-token', uri: '/livewire/update' })
        vi.stubGlobal('requestAnimationFrame', callback => callback())
        vi.stubGlobal('Alpine', { transaction: callback => Promise.resolve(callback()) })
    })

    afterEach(() => {
        vi.unstubAllGlobals()
        vi.restoreAllMocks()
    })

    it('discards an older live model response after a newer response arrives', async () => {
        let component = createComponent()
        let requests = mockRequests()

        component.value = 'j'
        let firstAction = fireLiveModelAction(component)
        await requests.waitFor(1)

        component.value = 'jk'
        let secondAction = fireLiveModelAction(component)
        await requests.waitFor(2)

        requests.respond(1, 'jk')
        await secondAction

        requests.respond(0, 'j')
        await firstAction

        expect(component.mergedValues).toEqual(['jk'])
    })

    it('accepts an older live model response when the newer request fails', async () => {
        let component = createComponent()
        let requests = mockRequests()

        component.value = 'j'
        let firstAction = fireLiveModelAction(component)
        await requests.waitFor(1)

        component.value = 'jk'
        let secondAction = fireLiveModelAction(component)
        let secondFailure = secondAction.catch(() => {})
        await requests.waitFor(2)

        requests.fail(1)
        await secondFailure

        requests.respond(0, 'j')
        await firstAction

        expect(component.mergedValues).toEqual(['j'])
    })

    it('discards an older live model response after a newer response is skipped', async () => {
        let component = createComponent()
        let requests = mockRequests()

        component.value = 'j'
        let firstAction = fireLiveModelAction(component)
        await requests.waitFor(1)

        component.value = 'jk'
        let secondAction = fireLiveModelAction(component)
        await requests.waitFor(2)

        requests.skip(1)
        await secondAction

        requests.respond(0, 'j')
        await firstAction

        expect(component.mergedValues).toEqual([])
    })

    it('accepts live model responses that arrive in order', async () => {
        let component = createComponent()
        let requests = mockRequests()

        component.value = 'j'
        let firstAction = fireLiveModelAction(component)
        await requests.waitFor(1)

        component.value = 'jk'
        let secondAction = fireLiveModelAction(component)
        await requests.waitFor(2)

        requests.respond(0, 'j')
        await firstAction

        requests.respond(1, 'jk')
        await secondAction

        expect(component.mergedValues).toEqual(['j', 'jk'])
    })
})

function fireLiveModelAction(component) {
    return fireAction(component, '$commit', [], { type: 'model.live' })
}

function mockRequests() {
    let requests = []

    vi.stubGlobal('fetch', vi.fn(() => {
        let request = deferred()

        requests.push(request)

        return request.promise
    }))

    return {
        async waitFor(count) {
            await vi.waitFor(() => expect(requests).toHaveLength(count))
        },

        respond(index, value) {
            requests[index].resolve(livewireResponse(value))
        },

        fail(index) {
            requests[index].reject(new Error('Network request failed'))
        },

        skip(index) {
            requests[index].resolve(livewireSkippedResponse())
        },
    }
}

function deferred() {
    let resolve
    let reject

    let promise = new Promise((resolvePromise, rejectPromise) => {
        resolve = resolvePromise
        reject = rejectPromise
    })

    return { promise, resolve, reject }
}

function livewireResponse(value) {
    return {
        ok: true,
        redirected: false,
        headers: { has: () => false },
        text: async () => JSON.stringify({
            components: [{
                snapshot: JSON.stringify({
                    data: { value },
                    memo: { id: 'request-test-component' },
                }),
                effects: { returns: [] },
            }],
            assets: [],
        }),
    }
}

function livewireSkippedResponse() {
    return {
        ok: true,
        redirected: false,
        headers: { has: () => false },
        text: async () => JSON.stringify({
            components: [{
                id: 'request-test-component',
                skip: true,
            }],
            assets: [],
        }),
    }
}

function createComponent() {
    return {
        id: 'request-test-component',
        el: document.createElement('div'),
        effects: {},
        value: '',
        mergedValues: [],
        snapshot: {
            data: { value: '' },
            memo: { id: 'request-test-component', async: [] },
        },
        islands: {},
        isIsolated: false,
        isLazy: false,

        getUpdates() {
            return { value: this.value }
        },

        getEncodedSnapshotWithLatestChildrenMergedIn() {
            return JSON.stringify(this.snapshot)
        },

        processEffects() {
            //
        },

        mergeNewSnapshot(snapshotEncoded) {
            let snapshot = JSON.parse(snapshotEncoded)

            this.snapshot = snapshot
            this.mergedValues.push(snapshot.data.value)
        },

        getDeepChildrenWithBindings() {
            //
        },
    }
}
