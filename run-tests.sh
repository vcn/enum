#!/bin/bash

echo "Running tests on PHP 7.2 ..." \
  && docker-compose run --rm phpunit-7.2 \
  && echo "Running tests on PHP 7.3 ..." \
  && docker-compose run --rm phpunit-7.3 \
  && echo "Running tests on PHP 7.4 ..." \
  && docker-compose run --rm phpunit-7.4 \
  && echo "Running tests on PHP 8.0 ..." \
  && docker-compose run --rm phpunit-8.0-rc
