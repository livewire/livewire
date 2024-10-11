
export function packUpPersistedPopovers(persistedEl) {
    persistedEl.querySelectorAll(':popover-open').forEach(el => {
        el.setAttribute('data-navigate-popover-open', '')

        let animations = el.getAnimations()

        // Gather any in-progress animations, serialize them, and pause them, for later re-triggering...
        el._pausedAnimations = animations.map(animation => ({
            keyframes: animation.effect.getKeyframes(),
            options: {
                duration: animation.effect.getTiming().duration,
                easing: animation.effect.getTiming().easing,
                fill: animation.effect.getTiming().fill,
                iterations: animation.effect.getTiming().iterations
            },
            currentTime: animation.currentTime,
            playState: animation.playState
        }))

        animations.forEach(i => i.pause())
    })
}

export function unPackPersistedPopovers(persistedEl) {
    persistedEl.querySelectorAll('[data-navigate-popover-open]').forEach(el => {
        el.removeAttribute('data-navigate-popover-open')

        // Wait for the popovers to be fully connected to the DOM...
        queueMicrotask(() => {
            if (! el.isConnected) return

            // Show them because disconnected popovers are force-hidden...
            el.showPopover()

            // End the out-of-the-box animations...
            el.getAnimations().forEach(i => i.finish())

            // If there are any paused animations, we need to re-trigger them...
            if (el._pausedAnimations) {
                el._pausedAnimations.forEach(({keyframes, options, currentTime, now, playState}) => {
                    let animation = el.animate(keyframes, options);

                    animation.currentTime = currentTime;
                })

                delete el._pausedAnimations
            }
        })
    })
}
