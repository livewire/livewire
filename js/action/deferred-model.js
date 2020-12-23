import Action from '.'

export default class extends Action {
    constructor(name, value, el, skipWatcher = false) {
        super(el, skipWatcher)

        this.type = 'syncInput'
        this.name = name
        this.payload = {
            name,
            value,
        }
    }
}
