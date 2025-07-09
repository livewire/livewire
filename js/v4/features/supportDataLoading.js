import interceptor from '@/v4/interceptors/interceptors.js'

interceptor.add(({el, directive, component, request}) => {
    el.setAttribute('data-loading', 'true')

    request.onFire(() => {
        console.log('fire')
    })

    request.onRequest(() => {
        console.log('request')
    })

    request.onBeforeResponse(() => {
        console.log('beforeResponse')
    })

    request.onResponse(() => {
        console.log('response')
        el.removeAttribute('data-loading')
    })

    request.onSuccess(() => {
        console.log('success')
    })

    request.onError(() => {
        console.log('error')
    })

    request.onCancel(() => {
        console.log('cancel')
        el.removeAttribute('data-loading')
    })

    request.onBeforeMorph(() => {
        console.log('beforeMorph')
    })

    request.onAfterMorph(() => {
        console.log('afterMorph')
    })
})
