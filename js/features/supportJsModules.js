import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (scriptModuleHash) {
        let encodedName = component.name.replace('.', '--').replace('::', '---').replace(':', '----')
        let path = `/livewire/js/${encodedName}.js?v=${scriptModuleHash}`

        import(path).then(module => {
            module.run.call(component.$wire, component.$wire, component.$wire.js);
        });
    }
})
