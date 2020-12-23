import Message from '@/Message'

export default class extends Message {
    constructor(component, action) {
        super(component, [action])
    }

    get prefetchId() {
        return this.updateQueue[0].toId()
    }
}
