import Action from '.'

export default class extends Action {
    constructor(method, params, el, skipWatcher = false) {
        super(el, skipWatcher)

        this.type = 'callMethod'
        this.method = method
        this.payload = {
            method,
            params,
        }
    }
}
