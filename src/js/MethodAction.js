import Action from './Action'

export default class extends Action {
    constructor(method, params) {
        super()

        this.type = 'callMethod'
        this.payload = {
            method,
            params,
        }
    }
}
