import interceptor from '@/v4/interceptors/interceptors.js'

interceptor.add(({el, directive, component, request}) => {
    if (! directive || ! directive.modifiers.includes('preserve-scroll')) return

    request.onResponse(() => {
        let oldHeight = document.body.scrollHeight;
        let oldScroll = window.scrollY;

        setTimeout(() => {
            let heightDiff = document.body.scrollHeight - oldHeight;
            window.scrollTo(0, oldScroll + heightDiff);
        })
    })
})
