import { describe, it, vi, expect } from 'vitest'
import { fireAction } from './index'

describe('Request System', () => {
    // Test is incomplete, so we'll skip it for now...
    it.skip('should say hello world', async () => {
        global.fetch = vi.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve(JSON.stringify({
                    components: [
                        {
                            snapshot: JSON.stringify({
                                memo: { id: '1' },
                            }),
                        },
                    ],
                    assets: {},
                })),
            }),
        )

        let component = createComponent()

        let results = await fireAction(component, '$refresh')

        expect(results).toBe('hello world')
    })
})

function createComponent() {
    return {
        get el() {
            throw new Error('el not implemented')
        },

        id: '1',

        effects: {},

        snapshot: {
            memo: {
                id: '1',
            },
        },

        islands: {},

        getUpdates() {
            //
        },

        getEncodedSnapshotWithLatestChildrenMergedIn() {
            //
        },

        processEffects() {
            //
        },

        mergeNewSnapshot() {
            //
        },
    }
}