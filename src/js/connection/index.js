import { dispatch } from '../util'
import store from '../store';

export default class Connection {
    constructor(driver) {
        this.driver = driver

        this.driver.onMessage = (payload) => {
            this.onMessage(payload)
        }

        this.driver.refresh = (payload) => {
            this.refresh()
        }

        this.driver.init()
    }

    onMessage(payload) {
        store.findComponent(payload.id).receiveMessage(payload)

        dispatch('livewire:update')
    }

    sendMessage(message) {
        message.prepareForSend()

        this.driver.sendMessage(message.payload());
    }
}
