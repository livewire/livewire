import { describe, it, vi, expect } from 'vitest'
import { Directive } from './directives'
import Alpine from '../node_modules/alpinejs/dist/module.esm'

window.Alpine = Alpine

function createDirective(targetValue) {
    let el = document.createElement('div')
    el.setAttribute('wire:target', targetValue)

    return new Directive('target', [], 'wire:target', el)
}

describe('Parse out methods and params', () => {
    it('should parse a method only with no params', async () => {
        let targetValue = 'mountAction'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: [] }])
    })

    it('should parse a method with simple params', async () => {
        let targetValue = 'mountAction(1, 2)'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: [1, 2] }])
    })

    it('should parse a method with complex params', async () => {
        // the `JSON.parse()` block is generated using Laravel's `Js::from()` helper...
        let targetValue = `mountAction('add', JSON.parse('{\u0022block\u0022:\u0022name\u0022}'), { schemaComponent: 'tableFiltersForm.queryBuilder.rules' })`

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: ['add', { block: 'name' }, { schemaComponent: 'tableFiltersForm.queryBuilder.rules' }] }])
    })

    it('should throw error for missing closing parenthesis', async () => {
        let targetValue = 'mountAction(1, 2'

        let directive = createDirective(targetValue)

        expect(() => {
            directive.parseOutMethodsAndParams(targetValue)
        }).toThrow('Missing closing parenthesis for method "mountAction"')
    })

    it('should parse method with empty params', async () => {
        let targetValue = 'mountAction()'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: [] }])
    })

    it('should parse method with string params containing commas', async () => {
        let targetValue = 'mountAction("hello, world", \'test, value\')'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: ['hello, world', 'test, value'] }])
    })

    it('should parse method with escaped quotes in strings', async () => {
        let targetValue = 'mountAction("hello \\"world\\"", \'test \\\'value\\\'\')'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: ['hello "world"', 'test \'value\''] }])
    })

    it('should parse method with whitespace around params', async () => {
        let targetValue = 'mountAction( 1 , 2 , 3 )'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: [1, 2, 3] }])
    })

    it('should handle empty string input', async () => {
        let targetValue = ''

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([])
    })

    it('should handle whitespace-only input', async () => {
        let targetValue = '   '

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([])
    })

    it('should handle method with empty string parameters', async () => {
        let targetValue = 'mountAction("", \'\', "   ")'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([{ method: 'mountAction', params: ['', '', '   '] }])
    })

    it('should parse multiple methods without params', async () => {
        let targetValue = 'foo,bar'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([
                { method: 'foo', params: [] },
                { method: 'bar', params: [] }
            ])
    })

    it('should parse multiple methods without params with spaces', async () => {
        let targetValue = 'foo, bar'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([
                { method: 'foo', params: [] },
                { method: 'bar', params: [] }
            ])
    })

    it('should parse multiple methods with params', async () => {
        let targetValue = 'foo(1,2), bar(3,4)'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([
                { method: 'foo', params: [1, 2] },
                { method: 'bar', params: [3, 4] }
            ])
    })

    it('should parse mixed methods with and without params', async () => {
        let targetValue = 'foo, bar(1,2), baz'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([
                { method: 'foo', params: [] },
                { method: 'bar', params: [1, 2] },
                { method: 'baz', params: [] }
            ])
    })

    it('should parse multiple methods with complex params', async () => {
        // the `JSON.parse()` block is generated using Laravel's `Js::from()` helper...
        let targetValue = `foo('hello'), bar(JSON.parse('{\u0022key\u0022:\u0022value\u0022}')), baz(1,2,3)`

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([
                { method: 'foo', params: ['hello'] },
                { method: 'bar', params: [{ key: 'value' }] },
                { method: 'baz', params: [1, 2, 3] }
            ])
    })

    it('should handle extra whitespace around commas', async () => {
        let targetValue = 'foo , bar , baz'

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([
                { method: 'foo', params: [] },
                { method: 'bar', params: [] },
                { method: 'baz', params: [] }
            ])
    })

    it('should parse multiple calls to the same function with different params', async () => {
        let targetValue = `processFunction('1', 2), processFunction('3', 4)`

        let directive = createDirective(targetValue)
        let methods = directive.parseOutMethodsAndParams(targetValue)

        expect(methods)
            .toEqual([
                { method: 'processFunction', params: ['1', 2] },
                { method: 'processFunction', params: ['3', 4] }
            ])
    })
})
