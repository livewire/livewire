import { on } from '@/events'
import { dispatchSelf } from './supportEvents'

on('request', ({ options }) => {
    if (window.Echo) {
        options.headers['X-Socket-ID'] = window.Echo.socketId()
    }
})

on('effects', (component, effects) => {
    let listeners = effects.listeners || []

    listeners.forEach(event => {
        if (event.startsWith('echo')) {
            if (typeof window.Echo === 'undefined') {
                console.warn('Laravel Echo cannot be found')

                return
            }

            let event_parts = event.split(/(echo:|echo-)|:|,/)

            if (event_parts[1] == 'echo:') {
                event_parts.splice(2, 0, 'channel', undefined)
            }

            if (event_parts[2] == 'notification') {
                event_parts.push(undefined, undefined)
            }

            let [
                s1,
                signature,
                channel_type,
                s2,
                channel,
                s3,
                event_name,
            ] = event_parts

            if (['channel', 'private', 'encryptedPrivate'].includes(channel_type)) {
                window.Echo[channel_type](channel).listen(event_name, e => {
                    dispatchSelf(component, event, e)
                })
            } else if (channel_type == 'presence') {
                if (['here', 'joining', 'leaving'].includes(event_name)) {
                    window.Echo.join(channel)[event_name](e => {
                        dispatchSelf(component, event, e)
                    })
                }else{
                    window.Echo.join(channel).listen(event_name, e => {
                        dispatchSelf(component, event, e)
                    })
                }
            } else if (channel_type == 'notification') {
                window.Echo.private(channel).notification(notification => {
                    dispatchSelf(component, event, notification)
                })
            } else {
                console.warn('Echo channel type not yet supported')
            }
        }
    })
})
