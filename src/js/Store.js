import EventAction from "@/action/event";

const store = {
    componentsById: {},
    listeners: {},

    addComponent(component) {
        return this.componentsById[component.id] = component
    },

    findComponent(id) {
        return this.componentsById[id]
    },

    wipeComponents() {
        this.componentsById = {}
    },

    on(event, callback) {
        if (this.listeners[event] !== undefined) {
            this.listeners[event].push(callback)
        } else {
            this.listeners[event] = [callback]
        }
    },

    emit(event, ...params) {
        if (this.listeners[event] !== undefined) {
            this.listeners[event].forEach(callback => callback(...params))
        }

        this.componentsListeningForEvent(event).forEach(
            component => component.addAction(new EventAction(
                event, params
            ))
        )
    },

    componentsListeningForEvent(event) {
        return Object.keys(this.componentsById).map(key => {
            return this.componentsById[key]
        }).filter(component => {
            return component.events.includes(event)
        })
    },

    getBrowserId() {
        // window.name is persisted across page loads. It's a good way to identify a tab or window.
        if (! window.name) {
            window.name = this.makeid(5)
        }

        // window.pageId is not persisted across page loads.
        if (! window.pageId) {
            window.pageId = this.makeid(5)
        }

        return window.name + '.' + window.pageId
    },

    // From https://stackoverflow.com/questions/1349404/generate-random-string-characters-in-javascript
    makeid(length) {
        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
           result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
     }
}

export default store
