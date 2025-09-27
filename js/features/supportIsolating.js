import { on } from '@/hooks'
import { interceptPartition } from '@/request'

let componentsThatAreIsolated = new WeakSet

on('component.init', ({ component }) => {
    let memo = component.snapshot.memo

    // We only care about isolated components...
    if (memo.isolate !== true) return

    componentsThatAreIsolated.add(component)
})

interceptPartition(({ message, compileRequest }) => {
    // We only care about isolated components...
    if (! componentsThatAreIsolated.has(message.component)) return

    compileRequest([message])
})
