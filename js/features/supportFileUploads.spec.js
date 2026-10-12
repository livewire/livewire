import { beforeEach, afterEach, describe, it, expect, vi } from 'vitest'
import { upload, uploadMultiple } from './supportFileUploads'
import '@/features/supportIslands'
import '@/directives/wire-model'

let boundary = vi.hoisted(() => ({ origin: null, interceptors: [], model: null }))

vi.mock('@/request', () => ({
    setNextActionOrigin: origin => { boundary.origin = origin },
    setNextActionMetadata: () => {},
    interceptAction: callback => boundary.interceptors.push(callback),
    interceptMessage: () => {},
}))

vi.mock('@/directives', () => ({
    directive: (name, callback) => { if (name === 'model') boundary.model = callback },
}))

vi.mock('@/store', () => ({ findComponentByEl: el => el.component }))
vi.mock('@/directives/wire-dirty', () => ({ checkDirty: () => false }))
vi.mock('@/morph', () => ({ morphFragment: () => {}, getTagName: () => 'div' }))
vi.mock('@/fragment', () => ({
    closestFragment: el => el.dataset.island ? { metadata: { name: el.dataset.island } } : null,
}))

function uploadRuntime() {
    let calls = []
    let requests = []
    let listeners = new Map
    let component = {
        id: 'upload-component',
        name: 'file-upload-island',
        canonical: { file: null },
        $wire: {
            $on: (name, callback) => listeners.set(name, callback),
            $watch: () => () => {},
            call(method, ...params) {
                let action = {
                    origin: boundary.origin,
                    metadata: {},
                    mergeMetadata(metadata) { Object.assign(this.metadata, metadata) },
                }

                // Match the request layer's one-shot origin consumption.
                boundary.origin = null
                boundary.interceptors.forEach(callback => callback({ action }))
                calls.push({ method, params, ...action })

                return Promise.resolve()
            },
        },
    }

    vi.stubGlobal('XMLHttpRequest', class {
        constructor() {
            this.listeners = new Map
            this.upload = { addEventListener() {} }
            requests.push(this)
        }
        open() {}
        setRequestHeader() {}
        addEventListener(name, callback) { this.listeners.set(name, callback) }
        send() {}
        respond(status, payload) {
            this.status = status
            this.response = JSON.stringify(payload)
            this.listeners.get('load')()
        }
        fail() { this.listeners.get('error')() }
    })

    return {
        component, calls, requests,
        input(island = null, multiple = false) {
            let el = document.createElement('input')
            el.type = 'file'
            el.multiple = multiple
            el.component = component
            if (island) el.dataset.island = island
            let directive = { expression: 'file', modifiers: [] }

            boundary.model({ el, directive, component, cleanup: () => {} })

            return {
                el, directive,
                select(files = [new File(['contents'], 'document.txt')]) {
                    Object.defineProperty(el, 'files', { configurable: true, value: files })
                    el.dispatchEvent(new Event('change'))
                },
            }
        },
        signedUrl() { listeners.get('upload:generatedSignedUrl')({ name: 'file', url: '/upload' }) },
        signedS3Url() {
            listeners.get('upload:generatedSignedUrlForS3')({ name: 'file', payload: { url: '/upload', path: 'signed-upload', headers: {} } })
        },
        finish() { listeners.get('upload:finished')({ name: 'file', tmpFilenames: ['signed-upload'] }) },
    }
}

beforeEach(() => {
    boundary.origin = null
    document.head.innerHTML = '<meta name="csrf-token" content="test-token">'
})
afterEach(() => { vi.unstubAllGlobals() })

describe('native file uploads in islands', () => {
    it.each(['local', 's3'])('restores the input and directive for asynchronous %s completion', async storage => {
        let runtime = uploadRuntime()
        let input = runtime.input('file-upload')
        input.select()
        await Promise.resolve()
        if (storage === 's3') runtime.signedS3Url()
        else runtime.signedUrl()
        await Promise.resolve()
        runtime.requests[0].respond(200, { paths: ['signed-upload'] })

        expect(runtime.calls.map(call => call.method)).toEqual(['_startUpload', '_finishUpload'])
        expect(runtime.calls[0].params).toEqual(['file', [{ name: 'document.txt', size: 8, type: '' }], false])
        expect(runtime.calls[1].params).toEqual(['file', ['signed-upload'], false, false])
        for (let call of runtime.calls) {
            expect(call.origin).toEqual({ el: input.el, directive: input.directive })
            expect(call.metadata.island).toEqual({ name: 'file-upload', mode: 'morph' })
        }
    })

    it.each(['validation', 'network'])('targets the island on a %s transfer error', error => {
        let runtime = uploadRuntime()
        let input = runtime.input('file-upload')
        input.select()
        runtime.signedUrl()
        let errors = { errors: { 'files.0': ['Upload rejected.'] } }
        if (error === 'validation') runtime.requests[0].respond(422, errors)
        else runtime.requests[0].fail()

        expect(runtime.calls[1].method).toBe('_uploadErrored')
        expect(runtime.calls[1].params).toEqual(['file', error === 'validation' ? JSON.stringify(errors) : null, false])
        expect(runtime.calls[1].origin.el).toBe(input.el)
        expect(runtime.calls[1].metadata.island).toEqual({ name: 'file-upload', mode: 'morph' })
    })

    it('preserves the multiple-file payload and append behavior', () => {
        let runtime = uploadRuntime()
        let input = runtime.input('file-upload', true)
        input.select([new File(['first'], 'first.txt'), new File(['second'], 'second.txt')])
        runtime.signedUrl()
        runtime.requests[0].respond(200, { paths: ['first-upload', 'second-upload'] })

        expect(runtime.calls[0].params).toEqual(['file', [
            { name: 'first.txt', size: 5, type: '' },
            { name: 'second.txt', size: 6, type: '' },
        ], true])
        expect(runtime.calls[1].params).toEqual(['file', ['first-upload', 'second-upload'], true, true])
        expect(runtime.calls[1].origin.el).toBe(input.el)
        expect(runtime.calls[1].metadata.island).toEqual({ name: 'file-upload', mode: 'morph' })
    })

    it('keeps an independent origin for each upload queued against the same property', () => {
        let runtime = uploadRuntime()
        runtime.input('first-island').select()
        runtime.input('second-island').select()
        runtime.signedUrl()
        runtime.requests[0].respond(200, { paths: ['signed-upload'] })
        runtime.finish()
        runtime.signedUrl()
        runtime.requests[1].respond(200, { paths: ['second-upload'] })

        expect(runtime.calls.map(call => call.metadata.island?.name)).toEqual([
            'first-island', 'first-island', 'second-island', 'second-island',
        ])
    })

    it('keeps native inputs outside islands scoped to the base component', () => {
        let runtime = uploadRuntime()
        runtime.input().select()
        runtime.signedUrl()
        runtime.requests[0].respond(200, { paths: ['signed-upload'] })

        expect(runtime.calls.map(call => call.metadata)).toEqual([{}, {}])
    })

    it('supports programmatic uploads without an origin', () => {
        let runtime = uploadRuntime()
        upload(runtime.component, 'file', new File(['contents'], 'document.txt'))
        runtime.signedUrl()
        runtime.requests[0].respond(200, { paths: ['signed-upload'] })

        expect(runtime.calls.map(call => call.method)).toEqual(['_startUpload', '_finishUpload'])
        expect(runtime.calls.map(call => call.origin)).toEqual([null, null])
        expect(runtime.calls.map(call => call.metadata)).toEqual([{}, {}])
    })

    it('preserves programmatic multiple-upload replacement behavior', () => {
        let runtime = uploadRuntime()
        uploadMultiple(runtime.component, 'file', [new File(['contents'], 'document.txt')], () => {}, () => {}, () => {}, () => {}, false)
        runtime.signedUrl()
        runtime.requests[0].respond(200, { paths: ['signed-upload'] })

        expect(runtime.calls[1].params).toEqual(['file', ['signed-upload'], true, false])
        expect(runtime.calls[1].metadata).toEqual({})
    })
})
