import { on } from '@/hooks'

let componentsThatAreInterruptible = new WeakSet

on('component.init', ({ component }) => {
    let memo = component.snapshot.memo

    // We only care about interruptible components...
    if (memo.interruptible !== true) return

    componentsThatAreInterruptible.add(component)
})

on('commit.pooling', ({ commits }) => {
    commits.forEach(commit => {
        // We only care about interruptible components...
        if (! componentsThatAreInterruptible.has(commit.component)) return

        commit.interruptible = true
    })
})