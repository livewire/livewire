export function unwrap(object) {
    if (object === undefined) return undefined

    return JSON.parse(JSON.stringify(object))
}

export function batch(callback) {
    let batch = {
        queued: false,
        updates: {},
        add(updates) {
            Object.assign(batch.updates, updates)

            if (batch.queued) return

            batch.queued = true

            queueMicrotask(batch.flush)
        },

        flush() {
            if (Object.keys(batch.updates).length) {
                callback(batch.updates)
            }

            batch.queued = false
            batch.updates = {}
        },
    }

    return batch
}
