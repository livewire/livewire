import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    let hasModule = effects.hasScriptModule

    if (hasModule) {
        let encodedName = component.name.replace('.', '--').replace('::', '---').replace(':', '----')

        import(`/livewire/js/${encodedName}.js`).then(module => {
            module.run.call(component.$wire, [
                component.$wire,
                component.$wire.$js,
                component.$wire.$intercept
            ]);
        });
    }
})
