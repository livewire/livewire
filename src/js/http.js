import Axios from "axios";

export default {
    onMessage: null,

    init() {
        return this
    },

    wireUp() {
        //
    },

    sendMessage(payload) {
        Axios.post('/fake-websockets/message', payload)
            .then(response => {
                this.onMessage.call(this, response.data)
            })
    },
}
