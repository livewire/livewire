import LivewireElement from "../LivewireElement";

export default {
    loadingEls: [],
    loadingElsByRef: {},

    addLoadingEl(el, value, targetRef, remove) {
        if (targetRef) {
            if (this.loadingElsByRef[targetRef]) {
                this.loadingElsByRef[targetRef].push({el, value, remove})
            } else {
                this.loadingElsByRef[targetRef] = [{el, value, remove}]
            }
        } else {
            this.loadingEls.push({el, value, remove})
        }
    },

    removeLoadingEl(node) {
        const el = new LivewireElement(node)

        this.loadingEls = this.loadingEls.filter(({el}) => ! el.isSameNode(node))

        if (el.ref in this.loadingElsByRef) {
            delete this.loadingElsByRef[el.ref]
        }
    },

    setLoading(refs) {
        const refEls = refs.map(ref => this.loadingElsByRef[ref]).filter(el => el).flat()

        const allEls = this.loadingEls.concat(refEls)

        allEls.forEach(el => {
            if (el.remove) {
                el.el.classList.remove(el.value)
            } else {
                el.el.classList.add(el.value)
            }
        })

        return allEls
    },

    unsetLoading(loadingEls) {
        loadingEls.forEach(el => {
            if (el.remove) {
                el.el.classList.add(el.value)
            } else {
                el.el.classList.remove(el.value)
            }
        })
    },
}
