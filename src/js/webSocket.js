export default {
    onMessage: null,
    fallback: null,
    refreshDom: null,

    init() {
        return new Promise((resolve, reject) => {
            this.wsConnection = new WebSocket('ws://localhost:8080');

            this.wsConnection.onopen = () => {
                resolve(this)
            }

            this.wsConnection.onerror = e => {
                reject(e)
            }
        })
    },

    wireUp() {
        this.wsConnection.onclose = () => {
            console.log('retrying connection')
            setTimeout(() => {
                this.init()
                    .then(() => {
                        console.log('all good')
                        this.wireUp()
                        this.refreshDom()
                    })
                    .catch(() => {
                        console.log('didnt work, switching to http')
                        this.fallback()
                    })
            }, 400);
        }


        this.wsConnection.onmessage = e => {
            this.onMessage.call(this, JSON.parse(e.data))
        }
    },

    sendMessage(payload) {
        this.wsConnection.send(JSON.stringify(payload))
    }
}
