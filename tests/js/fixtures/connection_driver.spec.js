import { wait } from 'dom-testing-library'
import driver from './connection_driver.js'

describe('test driver', () => {
    test('has the properties of a driver', () => {
        expect(driver.sendMessage).not.toBe(undefined)
        expect(driver.onMessage).not.toBe(undefined)
        expect(driver.onError).not.toBe(undefined)
    })

    test('has the properties of a test driver', () => {
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

    describe('can simulate a response to sendMessage', () => {
        test('response is simulated if configured', async () => {
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

            driver.config = { response: { error: true } }
            driver.sendMessage('foo')

            await wait(() => {
                expect(spy).toHaveBeenCalled()
            })
        })
    })

    describe('can be configured to simulate multiple responses', () => {
        test('repeated sendMessage calls return responses sequentially', async () => {
            let spy = jest.fn()
            driver.onMessage = spy

            driver.config = { response: [ { foo: 'response 1' }, { foo: 'response 2' } ] }
            driver.sendMessage('foo')
            await wait(() => {
                expect(spy).toHaveBeenCalledTimes(1)
                expect(spy.mock.calls[0][0]).toMatchObject({ foo: 'response 1' })
            })
            driver.sendMessage('foo')
            await wait(() => {
                expect(spy).toHaveBeenCalledTimes(2)
                expect(spy.mock.calls[1][0]).toMatchObject({ foo: 'response 2' })
            })
            driver.sendMessage('foo')
            await wait(() => {
                expect(spy).toHaveBeenCalledTimes(3)
                expect(Object.keys(spy.mock.calls[2][0])).toEqual(['id', 'fromPrefetch'])
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
            driver.config = { response: { error: true } }
            driver.sendMessage({ id: 'theId', foo: 'bar' })
            await wait(() => {
                expect(spy).toHaveBeenCalledWith({ id: 'theId' })
            })
        })
    })
})
