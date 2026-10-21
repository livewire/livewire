import { describe, it, expect } from 'vitest'
import { generateWireObject } from '@/$wire'

describe('$wire proxy', () => {
    it('does not resolve inherited Object.prototype keys as properties or aliases', () => {
        let $wire = generateWireObject({ id: 'abc' }, { content: 'hello' })

        expect(() => $wire.constructor).not.toThrow()
        expect(() => $wire.toString).not.toThrow()
        expect(() => $wire.hasOwnProperty).not.toThrow()

        expect($wire.id).toBe('abc')
        expect($wire.$id).toBe('abc')
        expect($wire.content).toBe('hello')
    })

    it('resolves properties and aliases in browsers without Object.hasOwn', () => {
        let hasOwn = Object.hasOwn

        delete Object.hasOwn

        try {
            let $wire = generateWireObject({ id: 'abc' }, { content: 'hello' })

            expect($wire.id).toBe('abc')
            expect($wire.$id).toBe('abc')
            expect($wire.content).toBe('hello')
            expect(() => $wire.constructor).not.toThrow()
        } finally {
            Object.hasOwn = hasOwn
        }
    })
})
