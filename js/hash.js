import Alpine from 'alpinejs'

let hashMap = new WeakMap

Alpine.interceptInit(el => {
    Array.from(el.attributes)
        .filter((attr) => attr.name.startsWith('wire:model') && attr.name.endsWith('.hash'))
        .forEach(attr => {
            el.__livewire_ignore_self = true

            let hash = Math.random().toString(36).substring(2, 15)

            hashMap.set(el, { expression: attr.value, hash })

            el.__livewire_hash = hash

            Alpine.mutateDom(() => {
                el.removeAttribute(attr.name)
                el.setAttribute(attr.name.replace('.hash', ''), hash)
            })
        })
})

export function get(el) {
    return hashMap.get(el)
}

export function destroy(el) {
    hashMap.delete(el)
}
