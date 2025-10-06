
let componentSymbols = new WeakMap
let componentIslandSymbols = new WeakMap

export function scopeSymbolFromMessage(message) {
    let component = message.component

    let hasAllIslands = Array.from(message.actions).every(action => action.metadata.island)

    if (hasAllIslands) {
        let islandName = Array.from(message.actions).map(action => action.metadata.island.name).sort().join('|')

        let islandSymbols = componentIslandSymbols.get(component)

        if (! islandSymbols) {
            islandSymbols = { [islandName]: Symbol() }

            componentIslandSymbols.set(component, islandSymbols)
        }

        if (! islandSymbols[islandName]) {
            islandSymbols[islandName] = Symbol()
        }

        return islandSymbols[islandName]
    }

    if (! componentSymbols.has(component)) {
        componentSymbols.set(component, Symbol())
    }

    return componentSymbols.get(component)
}

export function scopeSymbolFromAction(action) {
    let component = action.component

    let isIsland = !! action.metadata.island

    if (isIsland) {
        let islandName = action.metadata.island.name

        let islandSymbols = componentIslandSymbols.get(component)

        if (! islandSymbols) {
            islandSymbols = { [islandName]: Symbol() }

            componentIslandSymbols.add(component, islandSymbols)
        }

        if (! islandSymbols[islandName]) {
            islandSymbols[islandName] = Symbol()
        }

        return islandSymbols[islandName]
    }

    if (! componentSymbols.has(component)) {
        componentSymbols.set(component, Symbol())
    }

    return componentSymbols.get(component)
}

export class MessageBus {
    pendingMessages = new Set
    activeMessages = new Set
    bufferingMessages = new Set

    constructor() {
        //
    }

    messageBuffer(message, callback) {
        if (this.bufferingMessages.has(message)) {
            return
        }

        this.bufferingMessages.add(message)

        setTimeout(() => { // Buffer for 5ms to allow other areas of the codebase to hook into the lifecycle of an individual commit...
            callback()

            this.bufferingMessages.delete(message)
        }, 5)
    }

    addPendingMessage(message) {
        this.pendingMessages.add(message)
    }

    clearPendingMessages() {
        this.pendingMessages.clear()
    }

    getPendingMessages() {
        return Array.from(this.pendingMessages)
    }

    addActiveMessage(message) {
        this.activeMessages.add(message)
    }

    removeActiveMessage(message) {
        this.activeMessages.delete(message)
    }

    findScopedPendingMessage(action) {
        return Array.from(this.pendingMessages).find(message => message.component === action.component)
    }

    activeMessageMatchingScope(action) {
        return Array.from(this.activeMessages).find(message => this.matchesScope(message, action))
    }

    matchesScope(message, action) {
        return message.scope === scopeSymbolFromAction(action)

        // let isSameComponent = message.component === action.component
        // let isIslandMessage = Array.from(message.actions).every(action => action.metadata.island)
        // let isIslandAction = !! action.metadata.island
        // let isSameIsland = !! isIslandMessage && isIslandAction && Array.from(message.actions).every(action => action.metadata.island.name === action.metadata.island.name)

        // if (! isSameComponent) return false

        // if (isIslandMessage && isIslandAction) {
        //     return isSameIsland
        // }

        // if (isIslandMessage && ! isIslandAction) {
        //     return false
        // }

        // if (! isIslandMessage && isIslandAction) {
        //     return false
        // }

        // return true
    }

    allScopedMessages(action) {
        return [...Array.from(this.activeMessages), ...Array.from(this.pendingMessages)].filter(message => {
            return this.matchesScope(message, action)
        })
    }

    eachPendingMessage(callback) {
        Array.from(this.pendingMessages).forEach(callback)
    }
}