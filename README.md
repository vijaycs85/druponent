# Component Drupal 8
Drupal 8 profile to create a drupal installation that consume NPM components and integrate with Drupal theme system.

# Getting started
To get started you will need:
* NPM (>= 4)
* Sample components can be found [here](https://www.npmjs.com/~eurostar-npm)

## Installation

1. Download/clone/composer crate 
```
cd Directory/you/want/to/work/into/it
git clone git@github.com:vijaycs85/druponent.git

```
2. Run build.sh
```
./build.sh
```
3. Visit the site and install drupal as usual.


## Structure
```
assets - Assets to build components.
gulp - build taks.
htdocs - Docroot of drupal installation.
--modules/custom
--profiles/custom
--themes/custom

```