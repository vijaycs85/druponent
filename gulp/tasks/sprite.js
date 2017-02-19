'use strict';

var gulp = require('gulp');
var svgSprite = require('gulp-svg-sprite');
var config = require('../config');
var handleErrors = require('../util/handleErrors');
var runSequence = require('run-sequence');
var del    = require('del');
var rename = require('gulp-rename');

// Sprite configuration settings
var spriteConfig = {
  shape         : {
    spacing: {
      padding: 1 // Prevent bug where icons can get cut off.
    }
  },
  mode: {
    css: {
      dest: '.',
      bust: true,
      prefix: '@mixin sprite-%s',
      sprite: 'sprite.svg',
      render: {
        scss: {
          dest: 'sprite',
          template: config.sprite.template
        }
      }
    }
  }
};

// Delete the sprite images folder.
gulp.task('sprite-clean', function () {
  return del(config.sprite.pre_sprite);
});

// Move images which will be sprited.
gulp.task('move-pre-sprite', function () {
  return gulp.src(config.sprite.imgs_to_sprite)
    .pipe(rename({dirname: ''}))
    .pipe(gulp.dest(config.sprite.pre_sprite));
});

// Create the sprite.
gulp.task('create-sprite', function () {
  return gulp.src(config.sprite.pre_sprite + '*.svg')
    .pipe(svgSprite(spriteConfig)
    .on('error', handleErrors.onError))
    .pipe(gulp.dest(config.dest.images));
});

// Move images which won't be sprited.
gulp.task('move-non-sprite', function () {
  return gulp.src(config.sprite.imgs_dont_sprite)
    .pipe(rename({dirname: ''}))
    .pipe(gulp.dest(config.dest.images));
});

gulp.task('sprite', function(callback) {
    runSequence('sprite-clean', 'move-pre-sprite', 'create-sprite', 'move-non-sprite', 'sprite-clean', callback);
  }
);
