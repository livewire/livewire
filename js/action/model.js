import Action from '.'

export default class extends Action {
    constructor(name, value, el) {
        super(el)

        this.isDefer = false
        this.type = 'syncInput'
        this.payload = {
            name,
            value,
        }
    }

    defer(condition = true) {
        this.isDefer = condition;

        return this
    }
}
