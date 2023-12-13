import { on } from '@/events'
import { dispatchSelf } from './supportEvents'

let mercureUrl = null

function getMercureUrl() {
    if (!mercureUrl && document.querySelector('[data-mercure-url]')) {
        mercureUrl = document
            .querySelector('[data-mercure-url]')
            .getAttribute('data-mercure-url')
    }
    return mercureUrl
}

on('effects', (component, effects) => {
    const listeners = effects.listeners || []

    listeners.forEach((event) => {
        if (event.startsWith('mercure')) {
            const mercureUrl = getMercureUrl()
            if (!mercureUrl) {
                console.warn(
                    "Warning: 'data-mercure-url' attribute not found. Ensure it is present in the HTML.",
                )
                return
            }

            if (typeof EventSource === 'undefined') {
                console.warn(
                    'Warning: EventSource API (for Mercure) not available. Check browser compatibility.',
                )
                return
            }

            const eventParts = event.split('mercure:')
            const [s1, topic] = eventParts
            const url = `${mercureUrl}?topic=${topic}`

            const mercureEventSource = new EventSource(url, {
                withCredentials: true,
            })

            mercureEventSource.onmessage = (e) => {
                const data = JSON.parse(e.data)
                dispatchSelf(component, event, [data])
            }

            mercureEventSource.onerror = (e) => {
                // console.log(e)
            }
        }
    })
})
