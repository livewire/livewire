this.$el.querySelector('[dusk="loaded"]').textContent = 'js-loaded'

let write = (text) => {
    this.$el.querySelector('[dusk="target"]').textContent = text
}

$js('mark', () => write('js-action-called'))

$js('markAgain', () => write('js-action-called-again'))
