import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    let hasModule = effects.hasJsModule

    if (hasModule) {
        import(`/livewire/js/${component.name.replace('.', '--')}.js`).then(module => {
            module.run.bind(component.$wire)();
        });
    }
})
