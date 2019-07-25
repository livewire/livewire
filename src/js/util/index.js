
export * from './debounce'
export * from './walk'
export * from './dispatch'

export function kebabCase(subject) {
    return subject.split(/[_\s]/).join("-").toLowerCase()
}

export function tap(output, callback) {
    callback(output)

    return output
}
