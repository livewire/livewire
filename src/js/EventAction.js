import Action from './Action'

export default class extends Action {
    constructor(name, params, childId) {
        super()

        this.type = 'fireEvent'
        this.payload = {
            name,
            params,
            childId,
        }
    }
}
