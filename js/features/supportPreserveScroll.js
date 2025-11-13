import { interceptMessage } from '@/request'

interceptMessage(({ actions, onSuccess }) => {
    onSuccess(({ onSync, onMorph, onRender }) => {
        actions.forEach(action => {
            let origin = action.origin

            if (! origin || ! origin.directive) return

            let directive = origin.directive

            if (! directive.modifiers.includes('preserve-scroll')) return

            let oldHeight
            let oldScroll

            onSync(() => {
                oldHeight = document.body.scrollHeight
                oldScroll = window.scrollY
            })

            onMorph(() => {
                let heightDiff = document.body.scrollHeight - oldHeight
                window.scrollTo(0, oldScroll + heightDiff)

                oldHeight = null
                oldScroll = null
            })
        })
    })
})
