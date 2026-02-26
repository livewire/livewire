import Alpine from 'alpinejs'

let supportedNavigateModifiers = ['hover', 'preserve-scroll', 'dirty-confirm']

let wireNavigateAttributes = generateNavigateAttributes()

// Export all wire:navigate variations for use in other files
export let wireNavigateSelectors = wireNavigateAttributes.map(attribute => `[${escapeSelectorAttribute(attribute)}]`)

// Combined selector for querying all wire:navigate elements
export let wireNavigateSelector = wireNavigateSelectors.join(', ')

// Register all selectors with Alpine
wireNavigateSelectors.forEach(selector => {
    Alpine.addInitSelector(() => selector)
})

Alpine.interceptInit(
    Alpine.skipDuringClone(el => {
        let wireNavigateAttribute = findNavigateAttribute(el)

        if (! wireNavigateAttribute) return

        let alpineDirective = wireNavigateAttribute.replace('wire:', 'x-')
        let expression = el.getAttribute(wireNavigateAttribute)
        let directiveValue = expression === '' ? true : expression

        Alpine.bind(el, { [alpineDirective]: directiveValue })
    })
)

document.addEventListener('alpine:navigating', () => {
    // Before navigating away, we'll inscribe the latest state of each component
    // in their HTML so that upon return, they will have the latest state...
    Livewire.all().forEach(component => {
        component.inscribeSnapshotAndEffectsOnElement()
    })
})

function findNavigateAttribute(el) {
    return Array
        .from(el.attributes)
        .map(attribute => attribute.name)
        .find(name => name === 'wire:navigate' || name.startsWith('wire:navigate.'))
}

function generateNavigateAttributes() {
    let attributes = ['wire:navigate']

    let subsetCount = 2 ** supportedNavigateModifiers.length

    for (let mask = 1; mask < subsetCount; mask++) {
        let selectedModifiers = supportedNavigateModifiers.filter((_, index) => {
            return (mask & (1 << index)) !== 0
        })

        generateModifierPermutations(selectedModifiers).forEach(permutation => {
            attributes.push(`wire:navigate.${permutation.join('.')}`)
        })
    }

    return attributes
}

function generateModifierPermutations(modifiers) {
    if (modifiers.length <= 1) return [modifiers]

    let permutations = []

    modifiers.forEach((modifier, index) => {
        let remainingModifiers = modifiers.filter((_, remainingIndex) => remainingIndex !== index)
        let subPermutations = generateModifierPermutations(remainingModifiers)

        subPermutations.forEach(subPermutation => {
            permutations.push([modifier, ...subPermutation])
        })
    })

    return permutations
}

function escapeSelectorAttribute(attribute) {
    return attribute
        .replaceAll(':', '\\:')
        .replaceAll('.', '\\.')
}
