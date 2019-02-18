var prefix = null;

module.exports = function () {
    if (prefix === null) {
        prefix = (
            document.querySelector('meta[name="livewire-prefix"]')
            || { content: 'wire' }
        ).content
    }

    return prefix
}
