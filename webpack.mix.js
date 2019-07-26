let mix = require('laravel-mix');

mix
    .js('src/js/index.js', 'livewire.js')
    .setPublicPath('dist')
    .version();

mix.webpackConfig({
    output: {
        libraryTarget: 'umd',
    }
});

mix.extend('aliasConfig', new class {
    webpackConfig(webpackConfig) {
        webpackConfig.resolve.extensions.push('.js', '.json', '.vue');
        webpackConfig.resolve.alias = {
            '@': __dirname + '/src/js',
            '@action': __dirname + '/src/js/action',
            '@component': __dirname + '/src/js/Component',
            '@connection': __dirname + '/src/js/connection',
            '@dom': __dirname + '/src/js/dom',
            '@drivers': __dirname + '/src/js/connection/drivers',
            '@message': __dirname + '/src/js/Message',
            '@morphdom': __dirname + '/src/js/dom/morphdom',
            '@node_initializer': __dirname + '/src/js/node_initializer',
            '@store': __dirname + '/src/js/Store',
            '@util': __dirname + '/src/js/util',
        };
    }
});
mix.aliasConfig();
