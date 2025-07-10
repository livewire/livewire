import interceptor from '@/v4/interceptors/interceptors.js'

interceptor.add(({el, directive, component, request}) => {
    el.setAttribute('data-loading', 'true')

    request.onResponse(() => {
        el.removeAttribute('data-loading')
    })

    request.onCancel(() => {
        el.removeAttribute('data-loading')
    })
})
