import { on } from '@/hooks'

let componentsThatAreIsolated = new WeakSet

on('component.init', ({ component }) => {
    let memo = component.snapshot.memo

    // We only care about isolated components...
    if (memo.isolate !== true) return

    componentsThatAreIsolated.add(component)
})

on('message.pooling', ({ messages }) => {
    messages.forEach(message => {
        // We only care about isolated components...
        if (! componentsThatAreIsolated.has(message.component)) return

        message.isolate = true
    })
})
