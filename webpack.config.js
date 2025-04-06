// File: webpack.config.js
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Main entry for JS and CSS (app.js should import app.css)
    .addEntry('app', './assets/app.js')

    // No longer need a separate entry for admin-sidebar.js
    // .addEntry('admin-sidebar', './assets/admin/admin-sidebar.js') // REMOVED

    // Create shared runtime chunk
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Uncomment if you use Sass/SCSS files
    // .enableSassLoader()

    // Enable jQuery globally
    //.autoProvidejQuery()

    // Configure Babel for modern JS features (optional but recommended)
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

// Example: Copy static assets (uncomment/adjust if needed)
// .copyFiles({
//     from: './assets/images',
//     to: 'images/[path][name].[hash:8].[ext]',
// })
;

module.exports = Encore.getWebpackConfig();