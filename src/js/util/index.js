
export * from '@util/debounce'
export * from '@util/walk'
export * from '@util/dispatch'

export function kebabCase(subject) {
    return subject.split(/[_\s]/).join("-").toLowerCase()
}

export function tap(output, callback) {
    callback(output)

    return output
}
