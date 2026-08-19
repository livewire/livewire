import { interceptMessage } from '@/request'
import { releaseAfterNavigation } from '@/plugins/navigate/navigation'

interceptMessage(({ message, onSend, onSuccess, onFinish }) => {
    let undos = []

    onSend(() => {
        message.actions.forEach(action => {
            let origin = action.origin

            if (! origin) return

            // Use targetEl if explicitly set (can be null to skip data-loading entirely)
            let el = origin.hasOwnProperty('targetEl') ? origin.targetEl : origin.el

            // Skip if no element to apply data-loading to
            if (! el) return

            el.setAttribute('data-loading', 'true')

            undos.push(() => {
                el.removeAttribute('data-loading')
            })
        })
    })

    releaseAfterNavigation({ onSuccess, onFinish }, () => undos.forEach(undo => undo()))
})
