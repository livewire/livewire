import Action from '.'

export default class extends Action {
    constructor(event, params, el) {
        super(el)

        this.type = 'fireEvent'
        this.payload = {
            event,
            params,
        }
    }
}
