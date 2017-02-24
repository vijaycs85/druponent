#!/bin/bash

pushd ./htdocs

if [ -z "$SKIP_COMPOSER" ]
then
  # delete and recreate composer core and contrib files
  rm -rf core
  rm -rf modules/contrib
  pushd ..

  if [ -n "$COMPOSER_UPDATE" ]
  then
    composer clear-cache
    composer update
  else
    composer install
  fi

  popd
fi

if [ -z "$SKIP_NPM" ]
then
  nvm install v4.4.7
  nvm use v4.4.7
  npm run install:dev
fi


popd
