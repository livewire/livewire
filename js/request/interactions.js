import { constructAction, createOrAddToOutstandingMessage, interceptAction, interceptPartition } from '@/request'

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
    interceptAction(({ action }) => {
        // Wire:click.renderless
        let isRenderless = action?.origin?.directive?.modifiers.includes('renderless')
        if (isRenderless) {
            action.metadata.renderless = true
        }

        let message = messageBus.activeMessageMatchingScope(action)

        if (message) {
            // Wire:click.async:
            // - allow async actions incoming to pass through...
            // - if active message actions are async, allow the incoming async action to pass through as well...
            if (message.isAsync() || action.isAsync()) return

            // Wire:poll:
            // - Throw away new polls if a request of any kind is in-flight...
            if (action.metadata.type === 'poll') {
                return action.cancel()
            }

            // Wire:poll:
            // - Cancel in-flight polls to prioritize the new poll...
            if (Array.from(message.actions).every(action => action.metadata.type === 'poll')) {
                return message.cancel()
            }

            // Wire:model.live:
            // - If both incoming and outgoing requests are model.live, let them run in parallel...
            if (Array.from(message.actions).every(action => action.metadata.type === 'model.live')) {
                if (action.metadata.type === 'model.live') {
                    return
                }
            }

            action.defer()

            message.addInterceptor(({ onFinish }) => {
                onFinish(() => action.fire())
            })
        }
    })
}
