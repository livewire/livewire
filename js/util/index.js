
export * from './debounce'
export * from './walk'
export * from './dispatch'

export function kebabCase(subject) {
    return subject.replace(/([a-z])([A-Z])/g, '$1-$2').replace(/[_\s]/, '-').toLowerCase()
}

export function tap(output, callback) {
    callback(output)

    return output
}
