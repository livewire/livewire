import { on } from '@/events'

let componentsThatWantToBeBundled = new WeakSet

on('component.init', ({ component }) => {
    let memo = component.snapshot.memo

    // We only care about lazy components...
    if (memo.lazyLoaded === undefined) return

    // The component doesn't want its lazy load to be an isolated request
    // then we'll mark it to detect at "pool" time to make sure it gets bundled...
    if (memo.lazyIsolated !== undefined && memo.lazyIsolated === false) {
        componentsThatWantToBeBundled.add(component)
    }
})

on('commit.pooling', ({ commits }) => {
    commits.forEach(commit => {
        if (componentsThatWantToBeBundled.has(commit.component)) {
            commit.isolate = false

            componentsThatWantToBeBundled.delete(commit.component)
        } else {
            commit.isolate = true
        }
    })
})
