import simulant from 'jsdom-simulant'
import http from 'httpConnection'
import NodeInitializer from 'NodeInitializer'
import ComponentManager from 'ComponentManager'
import Connection from 'Connection'

export function fireEventAndStopBeforeSendingToServer (selector, event, options) {
    http.sendMessage = jest.fn()

    const nodeInitializer = new NodeInitializer(new Connection(http))
    const livewire = new ComponentManager(nodeInitializer)
    livewire.init()

    simulant.fire(document.querySelector(selector), event, options)
}

export function fireEventAndExecuteCallbackWhileWaitingForServerToRespondWithDom (selector, event, callback, dom) {
    http.sendMessage = jest.fn(function({ id }) {
        callback()

        this.onMessage({ id, dom })
    })

    const nodeInitializer = new NodeInitializer(new Connection(http))
    const livewire = new ComponentManager(nodeInitializer)
    livewire.init()

    simulant.fire(document.querySelector(selector), event)
}

export function fireEventAndMakeServerRespondWithDom (selector, event, dom) {
    http.sendMessage = jest.fn(function({ id }) {
        this.onMessage({ id, dom })
    })

    const nodeInitializer = new NodeInitializer(new Connection(http))
    const livewire = new ComponentManager(nodeInitializer)
    livewire.init()

    simulant.fire(document.querySelector(selector), event)
}

export function fireEventAndGetPayloadBeingSentToServer (selector, event, options) {
    return new Promise((resolve) => {
        http.sendMessage = jest.fn(function (payload) {
            resolve(payload)
        })

        const nodeInitializer = new NodeInitializer(new Connection(http))
        const livewire = new ComponentManager(nodeInitializer)
        livewire.init()

        simulant.fire(document.querySelector(selector), event, options)
    })
}

export function fireEvent (selector, event, options) {
    simulant.fire(document.querySelector(selector), event, options)
}

export function callbackAndGetPayloadBeingSentToServer (callback) {
    return new Promise((resolve) => {
        http.sendMessage = jest.fn(function (payload) {
            resolve(payload)
        })

        const nodeInitializer = new NodeInitializer(new Connection(http))
        const livewire = new ComponentManager(nodeInitializer)
        livewire.init()

        callback()
    })
}
