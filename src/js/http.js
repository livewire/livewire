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
        Axios.post('/livewire/message', payload)
            .then(response => {
                this.onMessage.call(this, response.data)
            })
    },
}
