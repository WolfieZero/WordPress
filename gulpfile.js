// =============================================================================
// Gulpfile
// =============================================================================
'use strict';


// Variables
// =============================================================================

var elixir = require('laravel-elixir');
var theme  = 'app';

elixir.config.publicPath             = 'public/themes/' + theme;
elixir.config.versioning.buildFolder = '';
elixir.config.assetsPath             = 'src';
elixir.config.css.outputFolder       = 'assets';
elixir.config.css.sass.folder        = 'sass';
elixir.config.js.outputFolder        = 'assets';
elixir.config.js.folder              = 'js';
elixir.config.sourcemaps             = true;


// Elixir Tasks
// =============================================================================

elixir(function (mix) {

    mix.sass('app.scss');

    mix.browserify('app.js');

    mix.version([
        elixir.config.publicPath + '/styles/*.css',
        elixir.config.publicPath + '/scripts/*.js'
    ]);

    mix.browserSync({
        proxy: 'dev.box',
        files: [
            'public/themes/' + theme + '/**/*.php',
            elixir.config.publicPath + '/**/*.js',
            elixir.config.publicPath + '/**/*.css'
        ]
    });

});
