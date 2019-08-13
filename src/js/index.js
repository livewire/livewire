import componentStore from '@/Store'
import DOM from "@/dom/dom";
import Component from "@/Component";
import Connection from '@/connection'
import drivers from '@/connection/drivers'
import { ArrayFlat, ArrayFrom, ArrayIncludes, ElementGetAttributeNames } from '@/dom/polyfills';
import 'whatwg-fetch'
import 'promise-polyfill/src/polyfill';
import { dispatch } from './util';

class Livewire {
    constructor({ driver } = { driver: 'http' }) {
        if (typeof driver !== 'object') {
            driver = drivers[driver]
        }

        this.connection = new Connection(driver)
        this.components = componentStore
        this.onLoadCallback = () => {};

        this.activatePolyfills()
    }

    onLoad(callback) {
        this.onLoadCallback = callback
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

        this.onLoadCallback()
        dispatch('livewire:load')
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
        componentStore.beforeDomUpdate(callback)
    }

    afterDomUpdate(callback) {
        componentStore.afterDomUpdate(callback)
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
