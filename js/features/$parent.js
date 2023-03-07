import { closestComponent } from '@/store'
import { wireProperty } from '@/wire'

let memo

wireProperty('$parent', component => {
    if (memo) return memo.$wire

    let parent = closestComponent(component.el.parentElement)

    memo = parent

    return parent.$wire
})
