let nextActionOrigin = null

export function setNextActionOrigin(origin) {
    nextActionOrigin = origin
}

export function pullNextActionOrigin() {
    let origin = nextActionOrigin
    nextActionOrigin = null  // Self-clearing
    return origin || {}
}