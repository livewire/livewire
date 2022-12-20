import { deepClone } from "./synthetic/utils"

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

export function componentsByName(name) {
    return Object.values(state.components).filter(component => {
        debugger
        return name == component.name
    })
}

export function storeComponent(id, component) {
    state.components[id] = component
}

let releasePool = {}

export function releaseComponent(id) {
    let component = state.components[id]

    let effects = deepClone(component.synthetic.effects)
    delete effects['']['html']

    releasePool[id] = {
        effects,
        snapshot: deepClone(component.synthetic.snapshot)
    }

    delete state.components[id]
}

export function resurrect(id) {
    if (! releasePool[id]) {
        throw 'Cant find holdover resurrection component'
    }

    return releasePool[id]
}

export function find(id) {
    let component = state.components[id]

    return component && component.$wire
}

export function first() {
    return Object.values(state.components)[0].$wire
}

