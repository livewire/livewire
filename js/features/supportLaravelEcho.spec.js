import { afterEach, describe, it, expect } from 'vitest'
import { trigger } from '@/hooks'
import './supportLaravelEcho'

describe('Laravel Echo request headers', () => {
    afterEach(() => {
        delete window.Echo
    })

    it('removes the socket ID header when no socket ID is available', () => {
        window.Echo = { socketId: () => undefined }
        let options = { headers: { 'X-Socket-ID': 'stale-id' } }

        trigger('request', { options })

        expect(options.headers).not.toHaveProperty('X-Socket-ID')
    })

    it('sets the socket ID header when a socket ID is available', () => {
        window.Echo = { socketId: () => 'socket-id' }
        let options = { headers: {} }

        trigger('request', { options })

        expect(options.headers['X-Socket-ID']).toBe('socket-id')
    })
})