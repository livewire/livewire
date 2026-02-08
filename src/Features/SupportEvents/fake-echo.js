
window.fakeEchoListeners = []
window.fakeEchoLeftChannels = []

class FakeChannel {
    constructor(channel) {
        this.channel = channel

        this.type = 'public'
    }

    listen(eventName, callback) {
        window.fakeEchoListeners.push({
            channel: this.channel,
            event: eventName,
            type: this.type,
            callback,
        })

        return this
    }

    stopListening(eventName, callback) {
        window.fakeEchoListeners = window.fakeEchoListeners.filter(i => {
            if (callback) {
                return ! (i.event === eventName && i.callback === callback)
            }

            return ! (i.event === eventName)
        })


        return this
    }
}

class FakePrivateChannel extends FakeChannel {
    constructor(channel) {
        super(channel)

        this.type = 'private'
    }

    whisper(eventName, data) {
        return this
    }
}

class FakePresenceChannel extends FakeChannel {
    constructor(channel) {
        super(channel)

        this.type = 'presence'
    }

    here(callback) {
        window.fakeEchoListeners.push({
            channel: this.channel,
            event: 'here',
            type: this.type,
            callback,
        })

        return this
    }

    joining(callback) {
        window.fakeEchoListeners.push({
            channel: this.channel,
            event: 'joining',
            type: this.type,
            callback,
        })

        return this
    }

    whisper(eventName, data) {
        return this
    }

    leaving(callback) {
        window.fakeEchoListeners.push({
            channel: this.channel,
            event: 'leaving',
            type: this.type,
            callback,
        })

        return this
    }
}

class FakeEcho {
    join(channel) {
        return new FakePresenceChannel(channel);
    }

    channel(channel) {
        return new FakeChannel(channel);
    }

    private(channel) {
        return new FakePrivateChannel(channel);
    }

    encryptedPrivate(channel) {
        return new FakePrivateChannel(channel);
    }

    leave(channel) {
        window.fakeEchoLeftChannels.push(channel)

        window.fakeEchoListeners = window.fakeEchoListeners.filter(i => {
            return i.channel !== channel
        })
    }

    leaveChannel(channel) {
        window.fakeEchoLeftChannels.push(channel)

        window.fakeEchoListeners = window.fakeEchoListeners.filter(i => {
            return i.channel !== channel
        })
    }

    socketId() {
        return 'fake-socked-id'
    }

    // For dusk to trigger listeners...

    fakeTrigger({ channel, event, type, payload = {} }) {
        window.fakeEchoListeners.filter(listener => {
            if (event !== listener.event) return false
            if (channel !== listener.channel) return false
            if (type !== undefined && type !== listener.type) return false

            return true
        }).forEach(({ callback }) => {
            callback(payload)
        })
    }
}

window.Echo = new FakeEcho
