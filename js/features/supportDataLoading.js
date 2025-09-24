import { intercept } from '@/request'

intercept(({ action, component, request, el, directive }) => {
    if (! el) return

    // Don't add data-loading to poll directives...
    if (action.context.type === 'poll') return

    el.setAttribute('data-loading', 'true')

    request.afterResponse(() => {
        el.removeAttribute('data-loading')
    })

    request.onCancel(() => {
        el.removeAttribute('data-loading')
    })
})
