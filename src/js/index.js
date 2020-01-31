import componentStore from '@/Store'
import DOM from "@/dom/dom";
import Component from "@/component/index";
import Connection from '@/connection'
import drivers from '@/connection/drivers'
import '@/dom/polyfills/index';
import { dispatch } from './util';
import LoadingStates from '@/component/LoadingStates'
import DisableForms from '@/component/DisableForms'
import DirtyStates from '@/component/DirtyStates'
import OfflineStates from '@/component/OfflineStates'
import Polling from '@/component/Polling'
import UpdateQueryString from '@/component/UpdateQueryString'
import SupportVueJs from '@/component/SupportVueJs'

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
    }

    find(componentId) {
        return this.components.componentsById[componentId]
    }

    directive(name, callback) {
        this.components.registerDirective(name, callback)
    }

    hook(name, callback) {
        this.components.registerHook(name, callback)
    }

    onLoad(callback) {
        this.onLoadCallback = callback
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

    plugin(callable) {
        callable(this)
    }
}

if (! window.Livewire) {
    window.Livewire = Livewire
}

UpdateQueryString()
SupportVueJs()
LoadingStates()
DisableForms()
DirtyStates()
OfflineStates()
Polling()

dispatch('livewire:available')

export default Livewire
