import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    if (! effects['markClean']) return

    component.markAsClean(component.canonical)
})
