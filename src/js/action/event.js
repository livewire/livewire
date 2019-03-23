import Action from '.'

export default class extends Action {
    constructor(name, params, childId, el) {
        super(el)

        this.type = 'fireEvent'
        this.payload = {
            name,
            params,
            childId,
        }
    }
}
