import Livewire from 'laravel-livewire'
import driver from './connection_driver'

export default {
    configure(config)
    {
        this.dom = config.dom || ''
        this.asRoot = config.asRoot || false
        this.initialData = config.initialData || {}
        this.id = config.id || '123'
        this.requestInterceptor = config.requestInterceptor
        this.response = config.response
        this.error = config.error
        this.delay = config.delay
        this.directives = config.directives

        this.driver = { ...driver } // get new copy of the driver

        return this
    },

    mount(config = null) {
        if (config) this.configure(config)

        this.unmount()
        this.initializeDom()
        this.initializeDriver()
        this.initializeLivewire()
        this.registerDirectives()
        this.startLivewire()

        return document.body.firstElementChild
    },

    unmount() {
        window.livewire && window.livewire.stop()
        document.body.innerHTML = ''
    },

    initializeDom()
    {
        if (this.asRoot) {
            document.body.innerHTML = this.dom
        } else {
            document.body.innerHTML =
                `<div wire:id="${this.id}" wire:initial-data="${this.getInitialData()}">${this.dom}</div>`
        }
    },

    initializeDriver() {
        // make sure the simulated response dom has the test wire:id attribute
        if (this.response && this.response.dom && ! this.response.dom.includes('wire:id')) {
            this.response.dom = `<div wire:id="${this.id}">${this.response.dom}</div>`
        }

        this.driver.config = {
            requestInterceptor: this.requestInterceptor,
            response: this.response,
            error: this.error,
            delay: this.delay,
        }
    },

    initializeLivewire()
    {
        window.livewire = new Livewire({ driver: this.driver })
    },

    registerDirectives()
    {
        this.directives && this.directives.forEach(d =>
            window.livewire.directive(d.name, d.callback)
        )
    },

    startLivewire()
    {
        window.livewire.start()
    },

    getInitialData() {
        if (! this.initialData) this.initialData = {}

        if (typeof this.initialData === 'object' &&
            Object.keys(this.initialData).length > 0 &&
            ! Object.keys(this.initialData).includes('data')) {
            this.initialData = { data: this.initialData }
        }

        return JSON.stringify(this.initialData).replace(/\"/g, '&quot;')
    },
}
