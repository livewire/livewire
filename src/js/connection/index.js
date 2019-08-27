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

        // This prevents those annoying CSRF 419's by keeping the cookie fresh.
        // Yum! No one likes stale cookies...
        if (typeof this.driver.keepAlive !== 'undefined') {
            setInterval(() => {
                this.driver.keepAlive()
            }, 600000); // Every ten minutes.
        }

        this.driver.init()
    }

    onMessage(payload) {
        const { id, fromPrefetch } = payload
        if (fromPrefetch) {
            componentStore.findComponent(id).receivePrefetchMessage(payload)
        } else {
            componentStore.findComponent(id).receiveMessage(payload)

            dispatch('livewire:update')
        }
    }

    onError({ id }) {
        componentStore.findComponent(id).messageSendFailed()
    }

    sendMessage({ payload }) {
        this.driver.sendMessage(payload());
    }
}
