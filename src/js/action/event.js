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

    // Overriding toId() because some EventActions don't have an "el"
    toId() {
        return btoa(unescape(encodeURIComponent(this.type, this.payload.event, JSON.stringify(this.payload.params))))
    }
}
