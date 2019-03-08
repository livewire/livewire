import Message from './Message'

export default class extends Message {
    constructor(name, params, ref, component) {
        super(component.parent)

        this.ref = ref
        this.type = 'fireEvent'

        this.payloadPortion = {
            childId: component.id, name, params, ref
        }
    }
}
