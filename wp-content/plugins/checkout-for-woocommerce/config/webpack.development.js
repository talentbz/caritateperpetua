// Imports
const WebpackNotifierPlugin = require( 'webpack-notifier' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

module.exports = ( version ) => ( {
    output: {
        filename: `js/[name]-${version}.js`,
    },
    devtool: 'source-map',
    plugins: [
        new WebpackNotifierPlugin( { alwaysNotify: true } ),
        new MiniCssExtractPlugin( {
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: `css/[name]-${version}.css`,
        } ),
    ],
} );
