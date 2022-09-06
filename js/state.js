
export let state = {
    components: {},
}

export function hasComponent(id) {
    return !! state.components[id]
}

export function findComponent(id) {
    let component = state.components[id]

    if (! component) throw 'Component not found: '.id

    return component
}

export function storeComponent(id, component) {
    state.components[id] = component
}

export function releaseComponent(id) {
    delete state.components[id]
}

export function first() {
    return Object.values(state.components)[0].$wire
}
