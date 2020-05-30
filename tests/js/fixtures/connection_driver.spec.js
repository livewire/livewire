import { wait } from 'dom-testing-library'
import driver from './connection_driver.js'

describe('test driver', () => {
    test('maintains driver properties', () => {
        expect(driver.sendMessage).not.toBe(undefined)
        expect(driver.onMessage).not.toBe(undefined)
        expect(driver.onError).not.toBe(undefined)
        expect(driver.config).not.toBe(undefined)
    })

    describe('sendMessage', () => {
        test('calls a "requestInterceptor" if configured', () => {
            let spy = jest.fn()

            driver.sendMessage('foo')
            expect(spy).not.toHaveBeenCalled()

            driver.config = { requestInterceptor: spy }
            driver.sendMessage('foo')
            expect(spy).toHaveBeenLastCalledWith('foo')
        })
    })

    describe('response simulation', () => {
        test('response simulated if configured', async () => {
            let spy = jest.fn()
            driver.onMessage = spy

            driver.sendMessage('foo')
            await wait(() => {
                expect(spy).not.toHaveBeenCalled()
            })

            driver.config = { response: {} }
            driver.sendMessage('foo')
            await wait(() => {
                expect(spy).toHaveBeenCalled()
            })
        })

        test('delay can be configured', async () => {
            var requestTime, responseTime

            let spy = jest.fn(() => responseTime = Date.now())
            driver.onMessage = spy

            driver.config = { response: {} }
            requestTime = Date.now()
            driver.sendMessage()

            await wait(() => {
                expect(spy).toHaveBeenCalled()
                expect(responseTime-requestTime).toBeLessThan(10)
            })

            driver.config = { delay: 20, response: {} }
            requestTime = Date.now()
            driver.sendMessage()

            await wait(() => {
                expect(spy).toHaveBeenCalled()
                expect(responseTime-requestTime).toBeGreaterThan(10)
            })
        })

        test('calls onMessage hook if it exists', async () => {
            let spy = jest.fn()
            driver.onMessage = spy

            driver.config = { response: {} }
            driver.sendMessage('foo')

            await wait(() => {
                expect(spy).toHaveBeenCalled()
            })
        })

        test('calls onError hook if it exists and configured for an error', async () => {
            let spy = jest.fn()
            driver.onError = spy

            driver.config = { error: true }
            driver.sendMessage('foo')

            await wait(() => {
                expect(spy).toHaveBeenCalled()
            })
        })
    })

    describe('onMessage hook', () => {
        test('receives the id and fromPretch properties of the payload by default', async () => {
            let spy = jest.fn()

            driver.onMessage = spy
            driver.config = { response: {} }
            driver.sendMessage({ id: 'theId', fromPrefetch: true, foo: 'bar' })
            await wait(() => {
                expect(spy).toHaveBeenCalledWith({ id: 'theId', fromPrefetch: true })
            })
        })

        test('receives the config.response properties', async () => {
            let spy = jest.fn()

            driver.onMessage = spy
            driver.config = { response: { baz: 'something' } }
            driver.sendMessage({ id: 'theId', fromPrefetch: true, foo: 'bar' })
            await wait(() => {
                expect(spy).toHaveBeenCalledWith({ id: 'theId', fromPrefetch: true, baz: 'something' })
            })
        })
    })

    describe('onError hook', () => {
        test('receives the id of the payload', async () => {
            let spy = jest.fn()

            driver.onError = spy
            driver.config = { error: true }
            driver.sendMessage({ id: 'theId', foo: 'bar' })
            await wait(() => {
                expect(spy).toHaveBeenCalledWith({ id: 'theId' })
            })
        })
    })
})
