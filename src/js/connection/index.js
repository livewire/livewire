import { dispatch } from '../util'

export default class Connection {
    constructor(driver) {
        this.driver = driver

        // This is a very crude "locking" mechanism.
        // The problem scenerio:
        // A user clicks a button twice, sending two messages to the server
        // The first message's response hasn't been received before the second one is sent
        // Because of this, there are two problems:
        // A) The ui will do a "blip" update
        // B) The second message will send the same serialized object as the first message,
        //    effectively wiping out any component state the first message changed.
        // There are plenty of different strategies we can employ here, but I figure,
        // we'll start with the simplest: lock the UI until the most recent message is received.
        // This means, if a user clicks a button and the message round-trip takes a while,
        // the UI will not respond to any clicks while it's waiting. And not only that,
        // it will crudely discard any clicks made during that time. I personally think this is
        // a feature, so there is no "catch-up" that builds, like if we implemented a message queue.

        this.lockingMessage = null

        this.driver.onMessage = (payload) => {
            this.onMessage(payload)
        }

        this.driver.refresh = (payload) => {
            this.refresh()
        }

        this.driver.init()
    }

    onMessage(payload) {
        // For now, we can safely (I think) assume the message in the lock, is the appropriate
        // message to retrive. When we need something more sophisticated, we can simply send
        // the message's id in the request, and do some lookup to retreive it here.
        const message = this.lockingMessage

        message.storeResponse(payload)

        // Delegate to the component everything except handling the component
        // emiting an event, we'll handle that in the callback.
        message.component.receiveMessage(message)

        this.lockingMessage = null

        dispatch('livewire:update')
    }

    sendMessage(message) {
        if (this.lockingMessage !== null) {
            return
        }

        this.lockingMessage = message

        message.prepareForSend()

        this.driver.sendMessage(message.payload());
    }
}
