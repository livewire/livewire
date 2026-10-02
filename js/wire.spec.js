import { describe, it, expect, beforeAll } from 'vitest'
import { generateWireObject } from '@/$wire'

describe('$wire proxy', () => {
    let component

    beforeAll(() => {
        component = { name: 'foo', toJSON: () => ({}) }
    })

    it('does not treat inherited Object.prototype keys as $wire properties or aliases', () => {
        let $wire = generateWireObject(component, { content: '' })

        expect(() => $wire.constructor).not.toThrow()
        expect(() => $wire.hasOwnProperty).not.toThrow()
        expect(() => $wire.toString).not.toThrow()
        expect(() => $wire.valueOf).not.toThrow()
    })

    it('still resolves aliases and registered properties', () => {
        let $wire = generateWireObject(component, { content: 'hello' })

        expect($wire.__instance).toBe(component)
        expect($wire.content).toBe('hello')
    })
})
