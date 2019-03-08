import Message from './Message'

export default class extends Message {
    constructor(method, params, ref, component) {
        super(component)

        this.ref = ref
        this.type = 'callMethod'

        this.payloadPortion = {
            method, params, ref
        }
    }
}
