import { interceptMessage } from '@/request'

interceptMessage(({ actions, onSend, onCancel, onFailure, onError, onSuccess }) => {
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

    onCancel(() => undos.forEach(undo => undo()))
    onFailure(() => undos.forEach(undo => undo()))
    onError(() => undos.forEach(undo => undo()))
    onSuccess(() => undos.forEach(undo => undo()))
})
