import Alpine from 'alpinejs'

let secureMap = new WeakMap

Alpine.interceptInit(el => {
    Array.from(el.attributes)
        .filter((attr) => attr.name.startsWith('wire:model') && attr.name.endsWith('.hash'))
        .forEach(attr => {
            let hash = Math.random().toString(36).substring(2, 15)

            secureMap.set(el, { expression: attr.value, hash })

            el.__livewire_hash = hash

            Alpine.mutateDom(() => {
                el.removeAttribute(attr.name)
                el.setAttribute(attr.name.replace('.hash', ''), hash)
            })
        })
})

export function hashMap(el) {
    return secureMap.get(el)
}
