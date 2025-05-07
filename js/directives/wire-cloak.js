import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    if (el.hasAttribute('wire:cloak')) {
        Alpine.mutateDom(() => el.removeAttribute('wire:cloak'))
    }
})
