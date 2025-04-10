// File: webpack.config.js
const Encore = require('@symfony/webpack-encore');
const path = require('path'); // Make sure 'path' is required

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Main entry point (Ensure app.js imports bootstrap.js)
    .addEntry('app', './assets/app.js')

    // Create shared runtime chunk
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // --- RE-ADD THE ALIAS ---
    // This alias is REQUIRED by the loader invoked in bootstrap.js
    .addAliases({
        '@symfony/stimulus-bridge/controllers.json$': path.resolve(__dirname, 'assets/controllers.json')
        // Add any other aliases you might need here
    })
    // --- END ALIAS ---

    // --- Keep this commented out or removed if bootstrap.js handles loading ---
    // .enableStimulusBridge('./assets/controllers.json')
    // ---

    // Configure Babel
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23'; // Or your core-js version
    })

// .copyFiles(...) // If needed
;

module.exports = Encore.getWebpackConfig();