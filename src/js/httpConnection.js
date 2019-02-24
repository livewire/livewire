import Axios from "axios";

export default {
    onMessage: null,
    lastTimeARequestWasSent: null,

    init() {
        //
    },

    sendMessage(payload) {
        var timestamp = (new Date()).valueOf();
        this.lastTimeARequestWasSent = timestamp;

        // @todo - Figure out not relying on app's csrf stuff in bootstrap.js
        Axios.post('/livewire/message', payload)
            .then(response => {
                if (timestamp < this.lastTimeARequestWasSent) {
                    return
                }

                this.onMessage.call(this, response.data)
            })
            // @todo: catch 419 session expired.
    },
}
