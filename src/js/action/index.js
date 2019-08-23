
export default class {
    constructor(el) {
        this.el = el
    }

    get ref() {
        return this.el ? this.el.ref : null
    }

    toId() {
        return btoa(unescape(encodeURIComponent(this.el.el.outerHTML)))
    }
}
