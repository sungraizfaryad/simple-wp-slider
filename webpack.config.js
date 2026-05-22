const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        'admin/index':    path.resolve( __dirname, 'src/admin/index.js' ),
        'block/index':    path.resolve( __dirname, 'src/block/index.js' ),
        'frontend/index': path.resolve( __dirname, 'src/frontend/index.js' ),
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve( __dirname, 'assets/dist' ),
        filename: '[name].js',
        chunkFilename: 'chunks/[name].[contenthash:8].js',
    },
};
