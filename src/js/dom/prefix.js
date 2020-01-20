var prefix = null;

export default function () {
    if (prefix === null) {
        prefix = (
            document.querySelector('meta[name="livewire-prefix"]')
            || { content: 'wire' }
        ).content
    }

    return prefix
}
