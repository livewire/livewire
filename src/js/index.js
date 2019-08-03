import componentStore from '@/Store'
import DOM from "@/dom/dom";
import Component from "@/Component";
import Connection from '@/connection'
import drivers from '@/connection/drivers'

class Livewire {
    constructor({ driver } = { driver: 'http' }) {
        if (typeof driver !== 'object') {
            driver = drivers[driver]
        }

        this.connection = new Connection(driver)
        this.components = componentStore

        this.start()
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
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
