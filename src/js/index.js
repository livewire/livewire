import componentStore from '@/Store'
import DOM from "@/dom/dom";
import Component from "@/Component";
import Connection from '@/connection'
import drivers from '@/connection/drivers'
import { ArrayFlat, ArrayFrom, ArrayIncludes, ElementGetAttributeNames } from '@/dom/polyfills';
import 'whatwg-fetch'
import 'promise-polyfill/src/polyfill';

class Livewire {
    constructor({ driver } = { driver: 'http' }) {
        if (typeof driver !== 'object') {
            driver = drivers[driver]
        }

        this.connection = new Connection(driver)
        this.components = componentStore

        this.activatePolyfills()

        this.start()
    }

    activatePolyfills() {
        ArrayFlat();
        ArrayFrom();
        ArrayIncludes();
        ElementGetAttributeNames();
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
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
