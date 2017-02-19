'use strict';

var dest = './htdocs/ui/';
var src = './assets/src/';
var proxy = 'rdev.eurostar.com';

module.exports = {
  src: src,
  proxy: proxy,

  components: {
    src: [
      './jspm_packages/npm/eurostar-*/**/*.twig',
      './jspm_packages/npm/eurostar-*/**/*.json',
      '!./jspm_packages/npm/eurostar-*/node_modules/**/*'
    ],
    dest: './htdocs/ui/components/'
  },

  dest: {
    assets:     dest + 'assets/',
    components: dest + 'components/',
    images:     dest + 'images/'
  },

  sprite: {
    template          : src + 'sprite/sprite.scss.handlebars',
    imgs_to_sprite    : './jspm_packages/npm/eurostar-*/img-sprite/*.svg',
    imgs_dont_sprite  : './jspm_packages/npm/eurostar-*/img-dont-sprite/*.{jpg,png,svg}',
    pre_sprite        : src + 'sprite/pre-sprite/'
  }

};
