import { constructAction, createOrAddToOutstandingMessage, fireActionInstance, interceptAction, interceptPartition } from '@/request'

export function coordinateNetworkInteractions(messageBus) {
    // Handle isolated components...
    interceptPartition(({ message, compileRequest }) => {
        if (! message.component.isIsolated) return

        compileRequest([message])
    })

    // Handle lazy components...
    interceptPartition(({ message, compileRequest }) => {
        if (
            message.component.isLazy &&
            ! message.component.hasBeenLazyLoaded &&
            message.component.isLazyIsolated
        ) {
            compileRequest([message])
        }
    })

    // Handle modelable/reactive components...
    interceptPartition(({ message, compileRequest }) => {
        let component = message.component

        let bundledMessages = []

        component.getDeepChildrenWithBindings(child => {
            let action = constructAction(child, '$commit')
            let message = createOrAddToOutstandingMessage(action)

            bundledMessages.push(message)
        })

        if (bundledMessages.length > 0) {
            compileRequest([message, ...bundledMessages])
        }
    })

    // If a request is in-flight, queue up the action to fire after the in-flight request has finished...
    interceptAction(({ action, reject, defer }) => {
        let message = messageBus.activeMessageMatchingScope(action)

        if (message) {
            // Wire:click.async:
            // - allow async actions incoming to pass through...
            // - if active message actions are async, allow the incoming async action to pass through as well...
            let isAsync = action => action.origin?.directive?.modifiers.includes('async')
            let messageIsAsync = Array.from(message?.actions || []).every(isAsync)
            let actionIsAsync = isAsync(action)

            if (messageIsAsync || actionIsAsync) return

            // Wire:poll:
            // - Throw away new polls if a request of any kind is in-flight...
            if (action.metadata.type === 'poll') {
                return reject()
            }

            // Wire:poll:
            // - Cancel in-flight polls to prioritize the new poll...
            if (Array.from(message.actions).every(action => action.metadata.type === 'poll')) {
                message.cancel()
            }

            // Wire:model.live:
            // - If both incoming and outgoing requests are model.live, let them run in parallel...
            if (Array.from(message.actions).every(action => action.metadata.type === 'model.live')) {
                if (action.metadata.type === 'model.live') {
                    return
                }
            }

            defer()

            message.addInterceptor(({ onFinish }) => {
                onFinish(() => {
                    fireActionInstance(action)
                })
            })
        }
    })
}

// Scenarios:
// - Reactive/modelable
//   - When a child is mid-flight, what happens when the parent tries to send a request with the bundled child?
// - Wire:poll
//   - When a poll is in-flight, throw away any new polls...
//   - When a non-poll action comes in, cancel the in-flight poll...
//   - Should the very next unblocked poll wait the exact poll time before firing? Instead of just firing when it fires naturally which sometimes is immediately after the last poll is finished?
// - Wire:model.live
//   - When a model.live action is in-flight, cancel it with the new incoming action...
//   - Or should we allow both to run in parallel?
