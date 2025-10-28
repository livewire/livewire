import { interceptMessage } from '@/request'

interceptMessage(({ actions, onSend, onFinish }) => {
    let undos = []

    onSend(() => {
        actions.forEach(action => {
            let origin = action.origin

            if (! origin || ! origin.el) return

            if (action.metadata?.type === 'poll') return

            origin.el.setAttribute('data-loading', 'true')

            undos.push(() => {
                origin.el.removeAttribute('data-loading')
            })
        })
    })

    onFinish(() => undos.forEach(undo => undo()))
})
