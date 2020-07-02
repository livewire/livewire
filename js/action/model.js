import Action from '.'

export default class extends Action {
    constructor(name, value, el) {
        super(el)

        this.isPassive = false
        this.type = 'syncInput'
        this.payload = {
            name,
            value,
        }
    }

    passive(condition = true) {
        this.isPassive = condition;

        return this
    }
}
