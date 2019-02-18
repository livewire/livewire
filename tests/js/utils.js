import simulant from 'jsdom-simulant'
import NodeInitializer from 'NodeInitializer'
import ComponentManager from 'ComponentManager'
import Connection from 'Connection'
import rootsStore from 'rootsStore'

export function fireEventAndStopBeforeSendingToServer (selector, event, options) {
    const http = require('http')
    http.sendMessage = jest.fn()

    const nodeInitializer = new NodeInitializer(new Connection(http))
    const livewire = new ComponentManager(nodeInitializer)
    livewire.init()

    simulant.fire(document.querySelector(selector), event, options)
}

export function fireEventAndExecuteCallbackWhileWaitingForServerToRespondWithDom (selector, event, callback, dom) {
    const http = require('http')
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
    const http = require('http')
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
        const http = require('http')
        http.sendMessage = jest.fn(function (payload) {
            resolve(payload)
        })

        const nodeInitializer = new NodeInitializer(new Connection(http))
        const livewire = new ComponentManager(nodeInitializer)
        livewire.init()

        simulant.fire(document.querySelector(selector), event, options)
    })
}
