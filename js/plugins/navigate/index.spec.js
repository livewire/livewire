import { afterEach, describe, expect, it, vi } from 'vitest'
import { hasDirtyLivewireComponents, shouldProceedWithDirtyNavigationConfirmation } from './index'

let originalLivewire = window.Livewire
let originalConfirm = window.confirm

afterEach(() => {
    window.Livewire = originalLivewire
    window.confirm = originalConfirm
})

describe('Navigate dirty confirmation', () => {
    it('does not trigger confirmation when no component is dirty', () => {
        window.Livewire = {
            all: () => [{ canonical: { title: 'A' }, reactive: { title: 'A' } }],
        }
        window.confirm = vi.fn(() => false)

        expect(hasDirtyLivewireComponents()).toBe(false)
        expect(shouldProceedWithDirtyNavigationConfirmation()).toBe(true)
        expect(window.confirm).not.toHaveBeenCalled()
    })

    it('triggers confirmation when at least one component is dirty', () => {
        window.Livewire = {
            all: () => [
                { canonical: { title: 'A' }, reactive: { title: 'A' } },
                { canonical: { title: 'A' }, reactive: { title: 'B' } },
            ],
        }
        window.confirm = vi.fn(() => false)

        expect(hasDirtyLivewireComponents()).toBe(true)
        expect(shouldProceedWithDirtyNavigationConfirmation()).toBe(false)
        expect(window.confirm).toHaveBeenCalledOnce()
    })

    it('runs custom expression and cancels by default when dirty', () => {
        let evaluate = vi.fn()

        window.Livewire = {
            all: () => [{ canonical: { title: 'A' }, reactive: { title: 'B' } }],
        }
        window.confirm = vi.fn(() => true)

        let result = shouldProceedWithDirtyNavigationConfirmation({
            expression: 'isOpenDialog = true',
            evaluate,
            destination: new URL('https://example.com/posts'),
        })

        expect(result).toBe(false)
        expect(evaluate).toHaveBeenCalledOnce()
        expect(window.confirm).not.toHaveBeenCalled()
    })

    it('allows browser fallback confirmation inside custom expression', () => {
        let evaluate = vi.fn((expression, { scope }) => {
            scope.$fallbackConfirm()
        })

        window.Livewire = {
            all: () => [{ canonical: { title: 'A' }, reactive: { title: 'B' } }],
        }
        window.confirm = vi.fn(() => true)

        let result = shouldProceedWithDirtyNavigationConfirmation({
            expression: 'if (needsFallback) $fallbackConfirm()',
            evaluate,
            destination: new URL('https://example.com/posts'),
        })

        expect(result).toBe(true)
        expect(window.confirm).toHaveBeenCalledOnce()
    })
})
