import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
    interceptMessage: vi.fn(),
    morphFragment: vi.fn(),
}))

vi.mock('@/request', () => ({
    interceptMessage: mocks.interceptMessage,
}))

vi.mock('@/fragment', () => ({
    extractFragmentMetadataFromHtml: vi.fn(fragmentHtml => ({
        id: 'target-component',
        name: fragmentHtml,
        token: fragmentHtml,
    })),
    extractInnerHtmlFromFragmentHtml: vi.fn(fragmentHtml => fragmentHtml),
    findFragment: vi.fn(() => ({
        startMarkerNode: {},
        endMarkerNode: {},
    })),
}))

vi.mock('@/store', () => ({
    findComponent: vi.fn(() => ({ el: {} })),
}))

vi.mock('@/morph', () => ({
    morphFragment: mocks.morphFragment,
}))

await import('./supportSlots')

describe('supportSlots', () => {
    beforeEach(() => {
        mocks.morphFragment.mockReset()
    })

    it('waits for every slot fragment to finish morphing', async () => {
        let resolveFirstMorph
        let resolveSecondMorph

        mocks.morphFragment
            .mockImplementationOnce(() => new Promise(resolve => resolveFirstMorph = resolve))
            .mockImplementationOnce(() => new Promise(resolve => resolveSecondMorph = resolve))

        let onMorph = captureOnMorph(['first-fragment', 'second-fragment'])
        let settled = false
        let morphing = onMorph().then(() => settled = true)

        expect(mocks.morphFragment).toHaveBeenCalledTimes(1)

        await Promise.resolve()

        expect(settled).toBe(false)

        resolveFirstMorph()
        await vi.waitFor(() => {
            expect(mocks.morphFragment).toHaveBeenCalledTimes(2)
        })

        expect(settled).toBe(false)

        resolveSecondMorph()
        await morphing

        expect(settled).toBe(true)
    })
})

function captureOnMorph(slotFragments) {
    let registerInterceptor = mocks.interceptMessage.mock.calls[0][0]
    let onSuccess
    let onMorph

    registerInterceptor({
        message: { component: {} },
        onSuccess: callback => onSuccess = callback,
        onStream: () => {},
    })

    onSuccess({
        payload: { effects: { slotFragments } },
        onMorph: callback => onMorph = callback,
    })

    return onMorph
}
