import { describe, it, expect } from 'vitest'
import { generateWireObject } from '@/$wire'
import AlpineCsp from '@alpinejs/csp'

function makeComponent() {
    let jsActions = {}

    let component = {
        id: 'abc',
        addJsAction(name, action) {
            jsActions[name] = action
        },
        hasJsAction(name) {
            return jsActions[name] !== undefined
        },
        getJsAction(name) {
            return jsActions[name].bind(component.$wire)
        },
        getJsActions() {
            let actions = {}

            for (let name of Object.keys(jsActions)) {
                actions[name] = component.getJsAction(name)
            }

            return actions
        },
    }

    return component
}

describe('$js actions in the CSP build', () => {
    it('can be called from an expression after the evaluator rebinds functions', () => {
        let calls = []

        let component = makeComponent()
        let $wire = generateWireObject(component, {})
        component.$wire = $wire

        $wire.$js('nextTab', (el) => calls.push(el.id))

        let el = document.createElement('button')
        el.id = 'one'

        let scope = { $js: $wire.$js, $wire, $el: el }

        // The CSP build's evaluator binds any function value before reading
        // members from it, which strips the $js action properties...
        AlpineCsp.evaluateRaw(el, '$js.nextTab($el)', { scope })
        AlpineCsp.evaluateRaw(el, '$wire.$js.nextTab($el)', { scope })
        AlpineCsp.evaluateRaw(el, '$wire.$js["nextTab"]($el)', { scope })

        expect(calls).toEqual(['one', 'one', 'one'])
    })
})

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
})
