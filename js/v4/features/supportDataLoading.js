import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'

interceptorRegistry.add(({el, directive, component, request}) => {
    el.setAttribute('data-loading', 'true')

    request.afterResponse(() => {
        el.removeAttribute('data-loading')
    })

    request.onCancel(() => {
        el.removeAttribute('data-loading')
    })
})
