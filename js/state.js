
export let state = {
    components: {},
}

export function first() {
    return Object.values(state.components)[0].$wire
}
