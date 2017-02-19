'use strict';

var gulp         = require('gulp');
var sass         = require('gulp-sass');
var sassJspm     = require('sass-jspm-importer');
var autoprefixer = require('gulp-autoprefixer');
var minifycss    = require('gulp-minify-css');
var gutil        = require('gulp-util');
var handleErrors = require('../util/handleErrors');
var config       = require('../config');


gulp.task('component-styles', function () {
  return gulp.src(config.src + '*.scss')
    .pipe(sass({
      importer: sassJspm.importer
    })
    .on('error', handleErrors.onError))
    .pipe(autoprefixer({
      browsers: ['last 2 versions', 'ie 8', 'iOS 7']
    }))
    .pipe(global.isProd ? minifycss() : gutil.noop())
    .pipe(gulp.dest(config.dest.assets));
});
