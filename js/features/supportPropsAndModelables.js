import { fireActionSynchronouslyMidPartitionAndReturnMessage, interceptPartition } from '@/request'

interceptPartition(({ message, compileRequest }) => {
    let component = message.component

    let bundledMessages = [message]

    getDeepChildrenWithBindings(component, child => {
        bundledMessages.push(
            fireActionSynchronouslyMidPartitionAndReturnMessage(
                child, '$commit',
            ),
        )
    })

    compileRequest(bundledMessages)
})

function getDeepChildrenWithBindings(component, callback) {
    getDeepChildren(component, child => {
        if (hasReactiveProps(child) || hasWireModelableBindings(child)) {
            callback(child)
        }
    })
}

function hasReactiveProps(component) {
    let meta = component.snapshot.memo
    let props = meta.props

    return !! props
}

function hasWireModelableBindings(component) {
    let meta = component.snapshot.memo
    let bindings = meta.bindings

    return !! bindings
}

function getDeepChildren(component, callback) {
    component.children.forEach(child => {
        callback(child)

        getDeepChildren(child, callback)
    })
}
