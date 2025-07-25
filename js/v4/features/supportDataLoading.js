import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'

interceptorRegistry.add(({ action, component, request, el, directive }) => {
    if (! el) return

    // Don't add data-loading to poll directives...
    if (action.type === 'poll') return

    el.setAttribute('data-loading', 'true')

    request.afterResponse(() => {
        el.removeAttribute('data-loading')
    })

    request.onCancel(() => {
        el.removeAttribute('data-loading')
    })
})
