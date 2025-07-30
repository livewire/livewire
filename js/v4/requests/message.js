import { trigger } from '@/hooks'
import { morph } from '@/morph'
import { renderIsland } from '@/features/supportIslands'

export default class Message {
    updates = {}
    actions = []
    payload = {}
    context = {}
    interceptors = new Set()
    resolvers = []
    status = 'waiting'
    succeedCallbacks = []
    failCallbacks = []
    respondCallbacks = []
    finishTarget = null
    request = null
    isolate = false

    constructor(component) {
        this.component = component
    }

    addInterceptor(interceptor) {
        if (interceptor.hasBeenCancelled) return this.cancel()

        interceptor.cancel = () => this.cancel()

        this.interceptors.add(interceptor)
    }

    addContext(context) {
        this.context = {...this.context, ...context}
    }

    getContainer() {
        let isIsland = false
        let isComponent = false

        for (let action of this.actions) {
            if (action.getContainer() === 'island') {
                isIsland = true
            } else {
                isComponent = true
            }

            if (isIsland && isComponent) {
                return 'mixed'
            }
        }

        return isIsland ? 'island' : 'component'
    }

    pullContext() {
        let context = this.context

        this.context = {}

        return context
    }

    addAction(action, resolve) {
        // If the action isn't a magic action then it supersedes any magic actions.
        // Remove them so there aren't any unnecessary actions in the request...
        if (! this.isMagicAction(action.method)) {
            this.removeAllMagicActions()
        }

        if (this.isMagicAction(action.method)) {
            // If the action is a magic action and it already exists then remove the
            // old action so there aren't any duplicate actions in the request...
            // @todo: Should this happen now? What if the same action is called, but it has a different context?
            this.findAndRemoveAction(action.method)

            this.actions.push(action)

            // We need to store the resolver, so we can call all of the
            // magic action resolvers when the message is finished...
            this.resolvers.push(resolve)

            return
        }

        action.handleReturn = resolve

        this.actions.push(action)
    }

    getHighestPriorityType(actionTypes) {
        let rankedTypes = [
            'user',
            'refresh',
            'poll',
        ]

        // Find all action types that are in our ranked list
        let validActionTypes = actionTypes.filter(type => rankedTypes.includes(type))

        if (validActionTypes.length === 0) {
            return null
        }

        // Find the highest priority type (lowest index in rankedTypes)
        let highestPriorityType = validActionTypes.reduce((highest, current) => {
            let highestIndex = rankedTypes.indexOf(highest)
            let currentIndex = rankedTypes.indexOf(current)
            return currentIndex < highestIndex ? current : highest
        })

        return highestPriorityType
    }

    type() {
        let actionTypes = this.actions.map(i => i.context.type ?? 'user')
        return this.getHighestPriorityType(actionTypes)
    }

    magicActions () {
        return [
            '$refresh',
            '$set',
            '$sync',
            '$commit',
            '$clone',
        ]
    }

    isMagicAction(method) {
        return this.magicActions().includes(method)
    }

    removeAllMagicActions() {
        this.actions = this.actions.filter(i => !this.isMagicAction(i.method))
    }

    findAndRemoveAction(method) {
        this.actions = this.actions.filter(i => i.method !== method)
    }

    processCancellations(newRequest) {
        Array.from(newRequest.messages).forEach(newMessage => {
            if (this.component.id !== newMessage.component.id) return

            let existingMessageContainer = this.getContainer()
            let newMessageContainer = newMessage.getContainer()

            // If the containers are different, then just return...
            if (
                (existingMessageContainer === 'island' && newMessageContainer === 'component')
                || (existingMessageContainer === 'component' && newMessageContainer === 'island')
            ) {
                return
            }

            this.actions.forEach(existingAction => {
                newMessage.actions.forEach(newAction => {
                    let existingActionContainer = existingAction.getContainer()
                    let newActionContainer = newAction.getContainer()

                    // If the actions containers are different, then just return...
                    if (
                        (existingActionContainer === 'island' && newActionContainer === 'component')
                        || (existingActionContainer === 'component' && newActionContainer === 'island')
                    ) {
                        return
                    }

                    // If the action containers are both island, then we need to check if the islands are the same...
                    if (existingActionContainer === 'island' && newActionContainer === 'island') {
                        // If the islands are different, then just return...
                        if (existingAction.context.island.name !== newAction.context.island.name) {
                            return
                        }
                    }

                    let existingActionType = existingAction.context.type ?? 'user'
                    let newActionType = newAction.context.type ?? 'user'

                    // If both actions are polls we need to cancel the new one to let
                    // the old one finish so we don't end up in a polling loop...
                    if (existingActionType === 'poll' && newActionType === 'poll') {
                        return newMessage.cancel()
                    }

                    // If the existing action is a user action and the new action is a poll,
                    // then cancel the new one, as user actions are more important...
                    if (existingActionType === 'user' && newActionType === 'poll') {
                        return newMessage.cancel()
                    }

                    // Otherwise we can cancel the existing request and let the new one run...
                    return this.cancel()
                })
            })
        })
    }

    buffer() {
        this.status = 'buffering'
    }

    prepare() {
        trigger('message.prepare', { component: this.component })

        this.status = 'preparing'

        this.updates = this.component.getUpdates()

        let snapshot = this.component.getEncodedSnapshotWithLatestChildrenMergedIn()

        this.payload = {
            snapshot: snapshot,
            updates: this.updates,
            // @todo: Rename to "actions"...
            calls: this.actions.map(i => ({
                method: i.method,
                params: i.params,
                context: i.context,
            })),
        }

        // Allow other areas of the codebase to hook into the lifecycle
        // of an individual commit...
        this.finishTarget = trigger('commit', {
            component: this.component,
            commit: this.payload,
            succeed: (callback) => {
                this.succeedCallbacks.push(callback)
            },
            fail: (callback) => {
                this.failCallbacks.push(callback)
            },
            respond: (callback) => {
                this.respondCallbacks.push(callback)
            },
        })

        this.beforeSend()
    }

    beforeSend() {
        this.interceptors.forEach(i => i.beforeSend({ component: this.component, payload: this.payload }))
    }

    afterSend() {
        this.interceptors.forEach(i => i.afterSend({ component: this.component, payload: this.payload }))
    }

    beforeResponse(response) {
        this.interceptors.forEach(i => i.beforeResponse({ component: this.component, response }))
    }

    afterResponse(response) {
        this.interceptors.forEach(i => i.afterResponse({ component: this.component, response }))
    }

    respond() {
        this.respondCallbacks.forEach(i => i())
    }

    succeed(response) {
        if (this.isCancelled()) return

        this.status = 'succeeded'

        this.beforeResponse(response)

        this.respond()

        let { snapshot, effects } = response

        this.component.mergeNewSnapshot(snapshot, effects, this.updates)

        this.afterResponse(response)

        // Trigger any side effects from the payload like "morph" and "dispatch event"...
        this.component.processEffects(this.component.effects)

        this.resolvers.forEach(i => i())

        if (effects['returns']) {
            let returns = effects['returns']

            // Here we'll match up returned values with their method call handlers. We need to build up
            // two "stacks" of the same length and walk through them together to handle them properly...
            let returnHandlerStack = this.actions.map(({ handleReturn }) => (handleReturn))

            returnHandlerStack.forEach((handleReturn, index) => {
                handleReturn(returns[index])
            })
        }

        let parsedSnapshot = JSON.parse(snapshot)

        this.finishTarget({ snapshot: parsedSnapshot, effects })

        this.interceptors.forEach(i => i.onSuccess({ response }))

        this.succeedCallbacks.forEach(i => i(response))

        let html = effects['html']

        let islands = effects['islands']

        if (! html && ! islands) {
            setTimeout(() => {
                this.interceptors.forEach(i => i.returned())
            })

            return
        }

        this.interceptors.forEach(i => i.beforeRender({ component: this.component }))

        queueMicrotask(() => {
            if (html) {
                this.interceptors.forEach(i => i.beforeMorph({ component: this.component, el: this.component.el, html }))

                morph(this.component, this.component.el, html)

                this.interceptors.forEach(i => i.afterMorph({ component: this.component, el: this.component.el, html }))
            }

            if (islands) {
                islands.forEach(islandPayload => {
                    let { key, content, mode } = islandPayload

                    let island = this.component.islands[key]

                    this.interceptors.forEach(i => i.beforeMorphIsland({ component: this.component, island, content }))

                    renderIsland(this.component, key, content, mode)

                    this.interceptors.forEach(i => i.afterMorphIsland({ component: this.component, island, content }))
                })
            }

            setTimeout(() => {
                this.interceptors.forEach(i => i.afterRender({ component: this.component }))

                this.interceptors.forEach(i => i.returned())
            })
        })
    }

    error(e) {
        if (this.isCancelled()) return

        this.status = 'errored'

        this.respond()

        this.interceptors.forEach(i => i.onError({ e }))

        this.interceptors.forEach(i => i.returned())
    }

    fail(response, content) {
        if (this.isCancelled()) return

        this.status = 'failed'

        this.respond()

        this.interceptors.forEach(i => i.onFailure({ response, content }))

        this.failCallbacks.forEach(i => i())

        this.interceptors.forEach(i => i.returned())
    }

    cancel() {
        if (this.isSucceeded()) return

        this.status = 'cancelled'

        this.request?.cancelMessage(this)

        this.respond()

        this.interceptors.forEach(i => i.onCancel())

        this.interceptors.forEach(i => i.returned())
    }

    isBuffering() {
        return this.status === 'buffering'
    }

    isPreparing() {
        return this.status === 'preparing'
    }

    isSucceeded() {
        return this.status === 'succeeded'
    }

    isCancelled() {
        return this.status === 'cancelled'
    }

    isErrored() {
        return this.status === 'errored'
    }

    isFailed() {
        return this.status === 'failed'
    }

    isFinished() {
        return this.isSucceeded() || this.isCancelled() || this.isFailed() || this.isErrored()
    }
}
