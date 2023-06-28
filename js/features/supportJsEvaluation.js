import { overrideMethod } from '@/$wire'
import { on } from '@/events'

on('effects', (component, effects) => {
    let js = effects.js
    let xjs = effects.xjs

    if (js) {
        Object.entries(js).forEach(([method, body]) => {
            overrideMethod(component, method, () => {
                let func = new Function([], body)
                func.bind(component.$wire)()
            })
        })
    }

    if (xjs) {
        xjs.forEach(expression => {
            let func = new Function([], expression)
            func.bind(component.$wire)()
        })
    }
})
