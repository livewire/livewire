import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'

interceptorRegistry.add(({el, directive, component, request}) => {
    // Don't add data-loading to poll directives...
    if (directive.value === 'poll') return

    el.setAttribute('data-loading', 'true')

    request.afterResponse(() => {
        el.removeAttribute('data-loading')
    })

    request.onCancel(() => {
        el.removeAttribute('data-loading')
    })
})
