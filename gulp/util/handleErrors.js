'use strict';

var gutil = require('gulp-util');

function onError(error) {
    gutil.log(error.message);
    process.exit(1);
}

module.exports = {
  onError: onError
};
