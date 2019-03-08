import Message from './Message'

export default class extends Message {
    constructor(name, value, component) {
        super(component)

        this.type = 'syncInput'

        this.payloadPortion = {
            name, value
        }
    }
}
