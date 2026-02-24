import { describe, it, expect } from 'vitest'
import { applySyncDataFromServer, applySyncUpdatesFromServer, applySyncUpdatesToServer, registerSyncCodec } from './sync'

describe('Sync codecs', () => {
    it('casts built-in int values when hydrating from the server', () => {
        let data = { count: '42' }

        applySyncDataFromServer(data, { count: 'int' })

        expect(data.count).toBe(42)
    })

    it('only applies sync transforms on root update keys', () => {
        let updates = {
            count: '8',
            'count.value': '9',
        }

        let transformed = applySyncUpdatesToServer(updates, { count: 'int' })

        expect(transformed).toEqual({
            count: 8,
            'count.value': '9',
        })
    })

    it('supports custom codecs in both directions', () => {
        let unregister = registerSyncCodec('App\\Support\\MoneyCodec', {
            fromServer: (value) => ({ amount: value.amount, formatted: `$${value.amount / 100}` }),
            toServer: (value) => ({ amount: value.amount }),
        })

        let hydrated = applySyncDataFromServer(
            { price: { amount: 1234 } },
            { price: 'App\\Support\\MoneyCodec' }
        )

        let dehydrated = applySyncUpdatesToServer(
            { price: { amount: 5678, formatted: '$56.78' } },
            { price: 'App\\Support\\MoneyCodec' }
        )

        let roundTrip = applySyncUpdatesFromServer(
            dehydrated,
            { price: 'App\\Support\\MoneyCodec' }
        )

        expect(hydrated.price).toEqual({ amount: 1234, formatted: '$12.34' })
        expect(dehydrated.price).toEqual({ amount: 5678 })
        expect(roundTrip.price).toEqual({ amount: 5678, formatted: '$56.78' })

        unregister()
    })
})
