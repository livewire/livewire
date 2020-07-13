import { dispatch } from '../util'
import componentStore from '../Store';

export default class Connection {
    constructor(driver) {
        this.driver = driver

        this.driver.onMessage = (payload) => {
            this.onMessage(payload)
        }

        this.driver.onError = (payload, status) => {
            return this.onError(payload, status)
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

    onError(payloadThatFailedSending, status) {
        componentStore.findComponent(payloadThatFailedSending.id).messageSendFailed()

        return componentStore.onErrorCallback(status)
    }

    sendMessage(message) {
        this.driver.sendMessage(message.payload());
    }
}
