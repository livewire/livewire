import { dispatch } from '../util'
import componentStore from '../store';

export default class Connection {
    constructor(driver) {
        this.driver = driver

        this.driver.onMessage = (payload) => {
            this.onMessage(payload)
        }

        this.driver.onError = (payload) => {
            this.onError(payload)
        }

        this.driver.init()
    }

    onMessage(payload) {
        componentStore.findComponent(payload.id).receiveMessage(payload)

        dispatch('livewire:update')
    }

    onError(payloadThatFailedSending) {
        componentStore.findComponent(payloadThatFailedSending.id).messageSendFailed()
    }

    sendMessage(message) {
        message.prepareForSend()

        this.driver.sendMessage(message.payload());
    }
}
