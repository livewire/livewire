module.exports = {
    presets: [
        [
            '@babel/preset-env',
            {
                targets: {
                    node: 'current',
                    ie: "11"
                },
            },
        ],
    ],
    plugins: ["@babel/plugin-proposal-object-rest-spread"]
};
