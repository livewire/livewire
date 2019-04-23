import drivers from './connection/drivers'
import Connection from './connection'
import store from './store'
import Component from "./component";
import LivewireElement from "./dom/element";
import NodeInitializer from "./node_initializer";
import EventAction from "./action/event";

class Livewire {
    constructor({ driver } = { driver: 'http' }) {
        if (typeof driver !== 'object') {
            driver = drivers[driver]
        }

        this.connection = new Connection(driver)
        this.nodeInitializer = new NodeInitializer
        this.components = store

        this.start()
    }

    emit(event, ...params) {
        this.components.componentsListeningForEvent(event).forEach(
            component => component.addAction(new EventAction(
                event, params
            ))
        )
    }

    restart() {
        this.stop()
        this.start()
    }

    stop() {
        this.components.wipeComponents()
    }

    start() {
        LivewireElement.rootComponentElements().forEach(el => {
            this.components.addComponent(
                new Component(el, this.nodeInitializer, this.connection)
            )
        })
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
