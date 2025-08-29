import { describe, it, expect } from 'vitest'
import { extractMethodsAndParamsFromRawExpression } from '../../js/utils.js'

describe('extractMethodAndParamsFromRawExpression', () => {
    it('should extract method name', () => {
        let expression = 'save'

        let result = extractMethodsAndParamsFromRawExpression(expression)

        expect(result).toEqual([{ method: 'save', params: [] }])
    })

    it('should extract multiple method names', () => {
        let expression = 'save, update'

        let result = extractMethodsAndParamsFromRawExpression(expression)

        expect(result).toEqual([
            { method: 'save', params: [] },
            { method: 'update', params: [] },
        ])
    })

    it('should extract parameters', () => {
        let expression = 'save(1)'

        let result = extractMethodsAndParamsFromRawExpression(expression)

        expect(result).toEqual([{ method: 'save', params: [1] }])
    })

    it('should extract multiple parameters', () => {
        let expression = 'save(1, 2), update(3, 4)'

        let result = extractMethodsAndParamsFromRawExpression(expression)

        expect(result).toEqual([
            { method: 'save', params: [1, 2] },
            { method: 'update', params: [3, 4] },
        ])
    })

    it('should extract params as runtime primitive values', () => {
        let expression = 'save("bar", 2), update(\'foo\', 4)'

        let result = extractMethodsAndParamsFromRawExpression(expression)

        expect(result).toEqual([
            { method: 'save', params: ['bar', 2] },
            { method: 'update', params: ['foo', 4] },
        ])
    })
})
