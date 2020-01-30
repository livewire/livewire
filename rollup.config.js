import md5 from 'md5'
import fs from 'fs-extra'
import outputManifest from 'rollup-plugin-output-manifest';
import babel from 'rollup-plugin-babel';
import filesize from 'rollup-plugin-filesize';
import { terser } from "rollup-plugin-terser";
import resolve from "rollup-plugin-node-resolve"
import alias from '@rollup/plugin-alias';
import commonjs from '@rollup/plugin-commonjs';

export default {
    input: 'src/js/index.js',
    output: {
        name: 'Livewire',
        file: 'dist/livewire.js',
        format: 'umd',
        sourcemap: true,
    },
    plugins: [
        resolve(),
        commonjs({
            // These npm packages still use common-js modules. Ugh.
            include: /node_modules\/(get-value|isobject|core-js)/,
        }),
        filesize(),
        terser({
            mangle: false,
            compress: {
                drop_debugger: false,
            },
        }),
        babel({
            exclude: 'node_modules/**'
        }),
        alias({
            entries: [
                { find: '@', replacement: __dirname + '/src/js' },
            ]
        }),
        // Mimic Laravel Mix's mix-manifest file for auto-cache-busting.
        outputManifest({
            serialize() {
                const file = fs.readFileSync(__dirname + '/dist/livewire.js', 'utf8');
                const hash = md5(file).substr(0, 20);

                return JSON.stringify({
                    '/livewire.js': '/livewire.js?id=' + hash,
                })
            }
        }),
    ]
}
