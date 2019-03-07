import store from './Store';

export default class Connection {
    constructor(driver) {
        this.driver = driver

        this.driver.onMessage = (payload) => {
            this.onMessage(payload)
        }

        this.driver.refresh = (payload) => {
            this.refresh()
        }
    }

    init() {
        this.driver.init()

        return this
    }

    onMessage(payload) {
        const { id, dom, dirtyInputs, serialized, redirectTo, ref, emitEvent } = payload
        const component = store.componentsById[id]

        if (redirectTo) {
            window.location.href = redirectTo
            return
        }

        component.replace(dom, dirtyInputs, serialized)

        ref && component.unsetLoading(ref)

        emitEvent && this.sendEvent(emitEvent.name, emitEvent.params, component)
    }

    sendMessage(data, component, minWait) {
        // This sends over lazilly updated wire:model attributes.
        data.data.syncQueue = component.syncQueue

        this.driver.sendMessage({
            ...data,
            ...{
                id: component.id,
                serialized: component.serialized,
            },
        }, minWait);

        component.clearSyncQueue()
    }

    refresh() {
        componentsStore.forEach(component => {
            this.sendMessage({ id: component.id, event: 'refresh' }, component)
        })
    }

    sendMethod(method, params, component, ref, minWait) {
        ref && component.setLoading(ref)

        this.sendMessage({
            type: 'callMethod',
            data: {
                method,
                params,
                ref,
            },
        }, component, minWait)
    }

    sendEvent(name, params, component, ref) {
        ref && component.setLoading(ref)

        this.sendMessage({
            type: 'fireEvent',
            data: {
                childId: component.id,
                name,
                params,
                ref,
            },
        }, component.parent)
    }

    sendModelSync(name, value, component) {
        this.sendMessage({
            type: 'syncInput',
            data: { name, value },
        }, component)
    }
}
