import componentStore from './store'
import DOM from "./dom/dom";
import Component from "./component";
import Connection from './connection'
import drivers from './connection/drivers'

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

    restart() {
        this.stop()
        this.start()
    }

    stop() {
        this.components.wipeComponents()
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
