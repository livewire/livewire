
document.addEventListener('alpine:init', () => {
    overrideReactivity()
    reactive = window.Alpine.reactive
    toRaw = window.Alpine.raw
})
