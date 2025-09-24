import { on } from '@/hooks'

let componentsThatWantToBeBundled = new WeakSet

let componentsThatAreLazy = new WeakSet

on('component.init', ({ component }) => {
    let memo = component.snapshot.memo

    // We only care about lazy components...
    if (memo.lazyLoaded === undefined) return

    componentsThatAreLazy.add(component)

    // The component doesn't want its lazy load to be an isolated request
    // then we'll mark it to detect at "pool" time to make sure it gets bundled...
    if (memo.lazyIsolated !== undefined && memo.lazyIsolated === false) {
        componentsThatWantToBeBundled.add(component)
    }
})

on('message.pooling', ({ messages }) => {
    messages.forEach(message => {
        // We only care about lazy components...
        if (! componentsThatAreLazy.has(message.component)) return

        if (componentsThatWantToBeBundled.has(message.component)) {
            message.isolate = false

            componentsThatWantToBeBundled.delete(message.component)
        } else {
            message.isolate = true
        }

        // Component is no longer lazy after the first full request, so remove it...
        componentsThatAreLazy.delete(message.component)
    })
})
