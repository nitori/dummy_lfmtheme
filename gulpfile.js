// node
var extend = require('extend');

// gulp
var gulp = require('gulp');
var sass = require('gulp-sass');
var cleanCSS = require('gulp-clean-css');
var rename = require('gulp-rename');
var requirejs = require('requirejs');
var postcss = require('gulp-postcss');
var sequence = require('gulp-sequence');
var lec = require('gulp-line-ending-corrector');

// requirejs configuration
var config = require('./Configuration/RequireJS/requirejs.json');
var lfmtheme = require('./lfmtheme');

for (var rpath in config.paths) {
    if (!config.paths.hasOwnProperty(rpath)) {
        continue;
    }
    config.paths[rpath] = lfmtheme.typo3path(config.paths[rpath]);
}

// add some optimize relevant configuration
config = extend({}, config, {
    wrapShim: true,
    name: 'LFM/Theme',
    out: 'Resources/Public/JavaScript/Distribution/Theme.min.js'
});

// -----
// TASKS

gulp.task('optimize', function () {
    requirejs.optimize(config);
});


/* ... other tasks omitted ... */

