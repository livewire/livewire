// Source: https://github.com/gcedo/eventsourcemock/blob/master/src/EventSource.js

class EventEmitter{
    constructor(){
        this.callbacks = {}
    }

    on(event, cb){
        if(!this.callbacks[event]) this.callbacks[event] = [];
        this.callbacks[event].push(cb)
    }

    emit(event, data){
        let cbs = this.callbacks[event]
        if(cbs){
            cbs.forEach(cb => cb(data))
        }
    }
}

const defaultOptions = {
    withCredentials: false,
}

const sources = {}

class EventSource {
    static CONNECTING
    static OPEN
    static CLOSED

    __emitter
    onerror
    onmessage
    onopen
    readyState
    url
    withCredentials
    sources

    constructor(url, configuration = defaultOptions) {
        this.url = url
        this.withCredentials = configuration.withCredentials
        this.readyState = 0
        this.__emitter = new EventEmitter()
        sources[url] = this
    }

    addEventListener(eventName, listener) {
        this.__emitter.addListener(eventName, listener)
    }

    removeEventListener(eventName, listener) {
        this.__emitter.removeListener(eventName, listener)
    }

    close() {
        this.readyState = 2
    }

    emit(eventName, messageEvent) {
        this.__emitter.emit(eventName, messageEvent)
    }

    emitError(error) {
        if (typeof this.onerror === 'function') {
            this.onerror(error)
        }
    }

    emitOpen() {
        this.readyState = 1
        if (typeof this.onopen === 'function') {
            this.onopen()
        }
    }

    emitMessage(message) {
        if (typeof this.onmessage === 'function') {
            this.onmessage(message)
        }
    }
}

EventSource.CONNECTING = 0
EventSource.OPEN = 1
EventSource.CLOSED = 2

Object.defineProperty(window, 'EventSource', {
    value: EventSource,
})
