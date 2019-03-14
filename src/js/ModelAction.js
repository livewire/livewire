import Action from './Action'

export default class extends Action {
    constructor(name, value) {
        super()

        this.type = 'syncInput'
        this.payload = {
            name,
            value,
        }
    }
}
