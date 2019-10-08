import componentStore from '@/Store'
import DOM from "@/dom/dom";
import Component from "@/Component/index";
import Connection from '@/connection'
import drivers from '@/connection/drivers'
import { ArrayFlat, ArrayFrom, ArrayIncludes, ElementGetAttributeNames } from '@/dom/polyfills';
import 'whatwg-fetch'
import 'promise-polyfill/src/polyfill';
import { dispatch } from './util';
import LoadingStates from '@/Component/LoadingStates'
import DirtyStates from '@/Component/DirtyStates'
import OfflineStates from '@/Component/OfflineStates'
import Polling from '@/Component/Polling'

class Livewire {
    constructor(options = {}) {
        const defaults = {
            driver: 'http'
        }

        options = Object.assign({}, defaults, options);

        const driver = typeof options.driver === 'object'
            ? options.driver
            : drivers[options.driver]

        this.connection = new Connection(driver)
        this.components = componentStore
        this.onLoadCallback = () => {};

        this.activatePolyfills()

        this.components.initializeGarbageCollection()
    }

    find(componentId) {
        return this.components.componentsById[componentId]
    }

    hook(name, callback) {
        this.components.registerHook(name, callback)
    }

    onLoad(callback) {
        this.onLoadCallback = callback
    }

    activatePolyfills() {
        ArrayFlat()
        ArrayFrom()
        ArrayIncludes()
        ElementGetAttributeNames()
    }

    emit(event, ...params) {
        this.components.emit(event, ...params)
    }

    on(event, callback) {
        this.components.on(event, callback)
    }

    restart() {
        this.stop()
        this.start()
    }

    stop() {
        this.components.tearDownComponents()
    }

    start() {
        DOM.rootComponentElementsWithNoParents().forEach(el => {
            this.components.addComponent(
                new Component(el, this.connection)
            )
        })

        this.onLoadCallback()
        dispatch('livewire:load')

        // This is very important for garbage collecting components
        // on the backend.
        window.addEventListener('beforeunload', () => {
            this.components.tearDownComponents()
        })

        document.addEventListener('visibilitychange', () => {
            this.components.livewireIsInBackground = document.hidden
        }, false);
    }

    rescan() {
        DOM.rootComponentElementsWithNoParents().forEach(el => {
            const componentId = el.getAttribute('id')
            if (this.components.hasComponent(componentId)) return

            this.components.addComponent(
                new Component(el, this.connection)
            )
        })
    }

    beforeDomUpdate(callback) {
        this.components.beforeDomUpdate(callback)
    }

    afterDomUpdate(callback) {
        this.components.afterDomUpdate(callback)
    }

    plugin(callable) {
        callable(this)
    }
}

if (! window.Livewire) {
    window.Livewire = Livewire
}

LoadingStates()
DirtyStates()
OfflineStates()
Polling()

dispatch('livewire:available')

export default Livewire
