'use strict';

/*
 * gulpfile.js
 * ===========
 * Rather than manage one giant configuration file responsible
 * for creating multiple tasks, each task has been broken out into
 * its own file in gulp/tasks. Any file in that folder gets automatically
 * required by the loop in ./support/ui/gulp (required below).
 *
 * To add a new task, simply add a new task file to gulp/tasks.
 */

global.isProd = false;

require('./gulp');