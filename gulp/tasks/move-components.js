'use strict';

var gulp = require('gulp');
var config = require('../config');
var rename = require('gulp-rename');
var jsonToYaml = require('gulp-json-to-yaml');
var runSequence = require('run-sequence');
var handleErrors = require('../util/handleErrors')

gulp.task('move', function(cb) {
  return gulp.src(config.components.src)
    .pipe(rename(function (path) {
      var dir = path.dirname;
      path.dirname = dir.replace(/@.*$/, '').replace(/@*/, '').replace(/^eurostar-/, '');
    })
    .on('error', handleErrors.onError))
    .pipe(gulp.dest(config.components.dest));
});

gulp.task('json-to-yaml', function (cb) {
  return gulp.src(['./htdocs/ui/components/**/*.json', '!./htdocs/ui/components/**/package.json'])
    .pipe(jsonToYaml()
    .on('error', handleErrors.onError))
    .pipe(gulp.dest(config.components.dest));
});

gulp.task('yaml-to-yml', function (cb) {
  return gulp.src(config.components.dest + '**/*.yaml')
    .pipe(rename(function (path) {
      path.extname = '.yml';
    })
    .on('error', handleErrors.onError))
    .pipe(gulp.dest(config.components.dest));
});

gulp.task('move-components', function (cb) {
  runSequence('move', 'json-to-yaml', 'yaml-to-yml', cb);
});
