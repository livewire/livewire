import drivers from './connection/drivers'
import Connection from './connection'
import ComponentManager from './component_manager'

class Livewire {
    constructor({ driver } = { driver: 'http' }) {
        if (typeof driver !== 'object') {
            driver = drivers[driver]
        }

        this.componentManager = new ComponentManager(
            new Connection(driver)
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
