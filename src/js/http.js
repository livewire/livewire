import Axios from "axios";

export default {
    onMessage: null,
    lastTimeARequestWasSent: null,

    wireUp() {
        //
    },

    sendMessage(payload) {
        var timestamp = (new Date()).valueOf();
        this.lastTimeARequestWasSent = timestamp;

        Axios.post('/livewire/message', payload)
            .then(response => {
                if (timestamp < this.lastTimeARequestWasSent) {
                    return
                }

                this.onMessage.call(this, response.data)
            })
    },
}
