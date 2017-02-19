'use strict';

var gulp = require('gulp');
var path = require('path');
var jspm = require('jspm');
var config = require('../config');

gulp.task('component-scripts', function(cb) {
  // load SystemJS config from file
  jspm.setPackagePath('.'); // optional
  var builder = new jspm.Builder();

  // Build a self-executing bundle (ie. Has SystemJS built in and auto-imports the 'app' module)
  builder.buildStatic(
    // src
    config.src + 'main',
    // dest
    config.dest.assets + 'app.js', {
      minify: global.isProd ? true : false,
      sourceMaps: global.isProd ? false : true
    }
  ).then(function() {
    cb();
  }).catch(function(err) {
    console.error(err);
  });
});
