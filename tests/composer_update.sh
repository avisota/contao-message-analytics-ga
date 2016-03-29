#!/usr/bin/env bash

composer remove avisota/contao-message --no-update
composer update --prefer-dist --no-interaction
composer require avisota/contao-message 3.2.x-dev
