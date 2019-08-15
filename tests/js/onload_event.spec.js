import Livewire from 'laravel-livewire'

test('window.livewire.onLoad callback is called when Livewire is initialized', async () => {
    var onLoadWasCalled = false

    window.livewire = new Livewire()

    window.livewire.onLoad(() => {
        onLoadWasCalled = true
    })

    window.livewire.start()

    expect(onLoadWasCalled).toBeTruthy()
})

test('livewire:load DOM event is fired after start', async () => {
    var loadEventWasFired = false

    window.livewire = new Livewire()

    document.addEventListener('livewire:load', () => {
        loadEventWasFired = true
    })

    window.livewire.start()

    expect(loadEventWasFired).toBeTruthy()
})
