import { on } from '@/hooks'

let componentsThatAreIsolated = new WeakSet

on('component.init', ({ component }) => {
    let memo = component.snapshot.memo

    // We only care about isolated components...
    if (memo.isolate !== true) return

    componentsThatAreIsolated.add(component)
})

on('commit.pooling', ({ commits }) => {
    commits.forEach(commit => {
        // We only care about isolated components...
        if (! componentsThatAreIsolated.has(commit.component)) return

        commit.isolate = true
    })
})
