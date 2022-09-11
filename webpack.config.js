const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     */
    .addEntry('app', './assets/app.js')

    .addEntry('agendamento', './assets/js/agendamento.js')
    .addEntry('agendamentoI', './assets/js/agendamentoi.js')
    .addEntry('agendamentoIndex','./assets/js/agendamentoIndex.js')
    .addEntry('agendamentoShow', './assets/js/agendamento_show.js')
    .addEntry('creditcard_mp', './assets/js/pagamento_card_mercadopago.js')
    .addEntry('pix_mp', './assets/js/pagamento_pix_mercadopago.js')

    .addEntry('funcionario', './assets/js/funcionario.js')
    .addEntry('funcionario_new', './assets/js/funcionario_new.js')

    .addEntry('configuracao', './assets/js/configuracao.js')
    .addEntry('configuracaoBloqueio', './assets/js/configuracaoBloqueio.js')
    .addEntry('empresaPagamentoProcessador', './assets/js/empresa_pagamento_processador.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    // .enableStimulusBridge('./assets/controllers.json')

    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    //.enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
