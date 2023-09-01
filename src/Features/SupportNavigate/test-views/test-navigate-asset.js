
if (! window._lw_dusk_asset_count) {
    window._lw_dusk_asset_count = 1
} else {
    window._lw_dusk_asset_count++
}

document.addEventListener('livewire:navigated', () => {
    console.log('Hello!')
    if (! window._lw_dusk_navigated_event_count) {
        window._lw_dusk_navigated_event_count = 1
    } else {
        window._lw_dusk_navigated_event_count++
    }
})
