import { on } from '@/hooks'
import { getUriPrefix } from '@/utils'

on('effect', ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule

    if (scriptModuleHash) {
        let encodedName = component.name.replace('.', '--').replace('::', '---').replace(':', '----')
        let path = `${getUriPrefix()}/js/${encodedName}.js?v=${scriptModuleHash}`

        import(path).then(module => {
            module.run.call(component.$wire, component.$wire, component.$wire.js);
        });
    }
})
