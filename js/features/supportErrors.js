// This errors object has the most common methods from \Illuminate\Support\MessageBag class on the backend...
import Alpine from 'alpinejs'

export function getErrorsObject(component) {
    if (! component.__errorsVersion) {
        component.__errorsVersion = Alpine.reactive({ v: 0 })
    }

    let version = component.__errorsVersion

    return {
        messages() {
            version.v
            return component.snapshot.memo.errors
        },

        keys() {
            return Object.keys(this.messages())
        },

        has(...keys) {
            if (this.isEmpty()) return false

            if (keys.length === 0 || (keys.length === 1 && keys[0] == null)) return this.any()

            if (keys.length === 1 && Array.isArray(keys[0])) keys = keys[0]

            for (let key of keys) {
                if (this.first(key) === '') return false
            }

            return true
        },

        hasAny(keys) {
            if (this.isEmpty()) return false

            if (keys.length === 1 && Array.isArray(keys[0])) keys = keys[0]

            for (let key of keys) {
                if (this.has(key)) return true
            }

            return false
        },

        missing(...keys) {
            if (keys.length === 1 && Array.isArray(keys[0])) keys = keys[0]

            return ! this.hasAny(keys)
        },

        first(key = null) {
            let messages = key === null ? this.all() : this.get(key)

            let firstMessage = messages.length > 0 ? messages[0] : ''

            return Array.isArray(firstMessage) ? firstMessage[0] : firstMessage
        },

        get(key) {
            version.v
            return component.snapshot.memo.errors[key] || []
        },

        all() {
            return Object.values(this.messages()).flat()
        },

        isEmpty() {
            return ! this.any()
        },

        isNotEmpty() {
            return this.any()
        },

        any() {
            return Object.keys(this.messages()).length > 0
        },

        count() {
            return Object.values(this.messages()).reduce((total, array) => {
                return total + array.length;
            }, 0);
        },

        clear(field = null) {
            if (field === null) {
                component.snapshot.memo.errors = {}
            } else {
                delete component.snapshot.memo.errors[field]
            }

            version.v++
        },
    }
}
