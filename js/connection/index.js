import { dispatch } from '../util'
import componentStore from '../Store';

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
        if (payload.fromPrefetch) {
            componentStore.findComponent(payload.id).receivePrefetchMessage(payload)
        } else {
            let component = componentStore.findComponent(payload.id)

            if (! component) {
                console.warn(`Livewire: Component [${payload.name}] triggered an update, but not found on page.`)
                return
            }

            component.receiveMessage(payload)

            dispatch('livewire:update')
        }
    }

    onError(payloadThatFailedSending) {
        componentStore.findComponent(payloadThatFailedSending.id).messageSendFailed()
    }

    sendMessage(message) {
        this.driver.sendMessage(message.payload());
    }
}
