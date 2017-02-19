'use strict';

var gulp        = require('gulp');
var runSequence = require('run-sequence');

/* TODO: Add ['clean'] task before development runs */
gulp.task('development', function(cb) {

  cb = cb || function() {};

  global.isProd = false;

  runSequence('sprite', 'component-styles', 'component-scripts', 'move-components', 'modernizr', cb);

});

// Assign the default task to development
gulp.task('default', ['development']);

// Assign a shortcut task to development
gulp.task('dev', ['development']);
