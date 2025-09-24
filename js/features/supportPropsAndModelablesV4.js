import { on } from '@/hooks'

// Ensure that all child components with reactive props (even deeply nested)
// are included in the network request...
on('message.pooling', ({ messages }) => {
    messages.forEach(message => {
        let component = message.component

        getDeepChildrenWithBindings(component, child => {
            child.$wire.$commit()
        })
    })
})

// Ensure that related components are pooled together, even if they chose
// to be isolated normally...
on('message.pooled', ({ requests }) => {
    let messages = getRequestsMessages(requests)

    messages.forEach(message => {
        let component = message.component

        getDeepChildrenWithBindings(component, child => {
            colocateRequestsByComponent(requests, component, child)
        })
    })
})

function getRequestsMessages(requests) {
    let messages = []

    requests.forEach(request => {
        request.messages.forEach(message => {
            messages.push(message)
        })
    })

    return messages
}

function colocateRequestsByComponent(requests, component, foreignComponent) {
    let request = findRequestWithComponent(requests, component)

    let foreignRequest = findRequestWithComponent(requests, foreignComponent)

    let foreignMessage = foreignRequest.findMessageByComponent(foreignComponent)

    // Delete needs to come before add in case there are the same request...
    foreignRequest.deleteMessage(foreignMessage)

    request.addMessage(foreignMessage)

    // Cleanup empty requests...
    requests.forEach(request => {
        if (request.isEmpty()) requests.delete(request)
    })
}

function findRequestWithComponent(requests, component) {
    return Array.from(requests).find(request => request.hasMessageFor(component))
}

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
