var prefix = null;

module.exports = function () {
    if (prefix === null) {
        console.log('hey')
        prefix = (
            document.querySelector('meta[name="livewire-prefix"]')
            || { content: 'livewire' }
        ).content
    }

    return prefix
}
