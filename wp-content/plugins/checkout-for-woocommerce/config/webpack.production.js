// Imports
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const WebpackShellPlugin = require( 'webpack-shell-plugin' );
const OptimizeCssAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );

module.exports = ( version, travis_build ) => {
    const productionDir = './dist';
    const outPath = `${productionDir}/checkout-for-woocommerce`;
    const zipName = `checkout-for-woocommerce-${version}.zip`;

    const production = {
        mode: 'production',
        output: {
            filename: `js/[name]-${version}.min.js`,
        },
        plugins: [
            new MiniCssExtractPlugin( {
                // Options similar to the same options in webpackOptions.output
                // both options are optional
                filename: `css/[name]-${version}.min.css`,
            } ),
            new OptimizeCssAssetsPlugin(),
        ],
    };

    if ( version !== false && !travis_build ) {
        production.plugins.push(
            new WebpackShellPlugin( {
                safe: true,
                onBuildStart: [
                    `rm -rf ${productionDir} && mkdir -p ${productionDir}`,
                ],
                onBuildEnd: [
                    `npx cpy --parents '.' '!./dist' '!./tests' '!./cypress' '!./**/node_modules' '!./cypress.env.json' '!./cypress.overrides.json' ${outPath} && cd ${productionDir} && zip --recurse-paths ${zipName} ./checkout-for-woocommerce`,
                ],
            } ),
        );
    }

    return production;
};
