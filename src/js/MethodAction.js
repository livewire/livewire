import Action from './Action'

export default class extends Action {
    constructor(method, params, el) {
        super(el)

        this.type = 'callMethod'
        this.payload = {
            method,
            params,
        }
    }
}
