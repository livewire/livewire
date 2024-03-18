import { on } from '@/hooks'

on('commit', ({ component: iComponent, commit: payload, respond }) => {
    if (iComponent !== component) return

    // TODO targets

    component.reactive.__loading = true

    respond(() => {
        component.reactive.__loading = false
    })
})