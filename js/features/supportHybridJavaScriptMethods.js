import { overrideMethod } from '@/$wire'
import { on } from '@/events'

on('effects', (component, effects) => {
    let js = effects.js
    if (! js) return

    Object.entries(js).forEach(([method, body]) => {
        overrideMethod(component, method, () => {
            let func = new Function([], body)
            func.bind(component.$wire)()
        })
    })
})
