import Action from '.'

export default class extends Action {
    constructor(method, params, el) {
        super(el)

        this.type = 'callMethod'
        this.method = method
        this.payload = {
            method,
            params,
        }
    }
}
