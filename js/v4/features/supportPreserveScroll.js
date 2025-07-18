import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'

interceptorRegistry.add(({el, directive, component, request}) => {
    if (! directive || ! directive.modifiers.includes('preserve-scroll')) return

    let oldHeight
    let oldScroll

    request.beforeRender(() => {
        oldHeight = document.body.scrollHeight;
        oldScroll = window.scrollY;
    })

    request.afterRender(() => {
        let heightDiff = document.body.scrollHeight - oldHeight
        window.scrollTo(0, oldScroll + heightDiff)

        oldHeight = null
        oldScroll = null
    })
})
