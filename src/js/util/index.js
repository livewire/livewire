
export * from './debounce'
export * from './dispatch'
export * from './add_mixin'
export * from './update_query_string'

export function kebabCase(subject) {
    return subject.split(/[_\s]/).join("-").toLowerCase()
}
