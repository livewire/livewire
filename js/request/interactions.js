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
            // - Normally we let overlapping model.live requests run in parallel so that typing
            //   quickly into an input doesn't have to wait for each keystroke's request to finish...
            // - The exception is when a component passes reactive/modelable data down to children.
            //   Those children get bundled into the parent's request along with a snapshot of the
            //   parent's state. Two parallel requests would each bundle the *same* (now stale)
            //   parent snapshot, so the second commit can clobber fresh state or throw
            //   "Cannot mutate reactive prop". In that case we skip the parallel fast-path and fall
            //   through to defer the incoming action until the in-flight one finishes — that way it
            //   bundles a fresh parent snapshot...
            let bothAreModelLive = action.metadata.type === 'model.live'
                && Array.from(message.actions).every(action => action.metadata.type === 'model.live')

            if (bothAreModelLive) {
                let incomingHasBoundChildren = componentHasBoundChildren(action.component)
                let outgoingHasBoundChildren = Array.from(message.actions).some(activeAction => componentHasBoundChildren(activeAction.component))

                if (! incomingHasBoundChildren && ! outgoingHasBoundChildren) return
            }

            action.defer()

            message.addInterceptor(({ onFinish }) => {
                onFinish(() => action.fire())
            })
        }
    })
}

// Does this component have any child components bound to it via reactive/modelable props?
// Those children get bundled into the component's network requests, which is what makes
// overlapping model.live requests unsafe (see the model.live note above)...
function componentHasBoundChildren(component) {
    let hasBoundChildren = false

    component.getDeepChildrenWithBindings(() => {
        hasBoundChildren = true
    })

    return hasBoundChildren
}
