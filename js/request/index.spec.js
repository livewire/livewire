import { describe, it, expect } from 'vitest'
import { interceptComponentAction, interceptComponentMessage, interceptComponentRequest } from './index'

// The two methods of a component (js/component.js) that its interceptors rely on.
function fakeComponent() {
    return {
        cleanups: [],
        addCleanup(cleanup) {
            this.cleanups.push(cleanup)

            return () => {
                let index = this.cleanups.indexOf(cleanup)

                if (index !== -1) this.cleanups.splice(index, 1)
            }
        },
        cleanup() {
            while (this.cleanups.length > 0) this.cleanups.pop()()
        },
    }
}

describe('Component interceptors', () => {
    it.each([
        ['action', interceptComponentAction],
        ['message', interceptComponentMessage],
        ['request', interceptComponentRequest],
    ])('a %s interceptor is removed with its component', (type, intercept) => {
        let component = fakeComponent()

        intercept(component, () => {})

        expect(component.cleanups).toHaveLength(1)

        component.cleanup()

        expect(component.cleanups).toHaveLength(0)
    })

    it.each([
        ['action', interceptComponentAction],
        ['message', interceptComponentMessage],
        ['request', interceptComponentRequest],
    ])('removing a %s interceptor by hand also takes it off its component', (type, intercept) => {
        let component = fakeComponent()

        let unsubscribe = intercept(component, 'save', () => {})

        unsubscribe()

        expect(component.cleanups).toHaveLength(0)
    })
})
