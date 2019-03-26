import drivers from './connection/drivers'
import Connection from './connection'
import ComponentManager from './component_manager'

class Livewire {
    constructor({ driver } = { driver: 'http' }) {
        // @todo - document.
        window.onpopstate = event => {
            if (event.state.livewirePath) {
                window.location.href = event.state.livewirePath
            }
        }

        this.componentManager = new ComponentManager(
            new Connection(drivers[driver])
        )

        this.start()
    }

    restart() {
        this.stop()
        this.start()
    }

    stop() {
        this.componentManager && this.componentManager.destroy()
    }

    start() {
        this.componentManager.mount()
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
