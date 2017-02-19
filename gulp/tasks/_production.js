'use strict';

var gulp        = require('gulp');
var runSequence = require('run-sequence');

/* TODO: Add ['clean'] task before production runs */
gulp.task('production', function(cb) {

  cb = cb || function() {};

  global.isProd = true;

  runSequence('sprite', 'component-styles', 'component-scripts', 'move-components', 'modernizr', cb);

});

// Assign a shortcut task to production
gulp.task('prod', ['production']);
