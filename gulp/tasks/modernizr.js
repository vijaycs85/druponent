'use strict';

var gulp        = require('gulp');
var modernizr   = require('gulp-modernizr');
var file        = require('gulp-file');
var fs          = require('fs');

gulp.task('modernizr', function() {
  // Load the Base Styles component's JSON file as it contains properties for the Modernizr build
  var baseStylesJson = JSON.parse(fs.readFileSync('./htdocs/ui/components/base-styles/modernizr-config.json'));

  // Create an empty file as a file is needed to be passed to the Modernizr task
  var str = "";
  return file('empty.js', str, { src: true })
    // Modernizr task
    .pipe(modernizr(baseStylesJson.modernizrConfig))
    .pipe(gulp.dest('./htdocs/ui/assets'))
});

module.exports = {
  modernizr: modernizr
};
