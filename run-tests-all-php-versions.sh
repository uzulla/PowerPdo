#!/bin/bash
set -e

echo "Running tests on multiple PHP versions..."

for version in 7.4 8.0 8.1 8.2 8.3; do
  echo "====================================="
  echo "Testing with PHP $version"
  echo "====================================="
  
  # Use the specific PHP version to run Composer and PHPUnit
  php$version -v
  echo "Installing dependencies with PHP $version..."
  php$version $(which composer) install
  
  echo "Running tests with PHP $version..."
  php$version vendor/bin/phpunit
  
  if [ $? -eq 0 ]; then
    echo "✅ Tests passed on PHP $version"
  else
    echo "❌ Tests failed on PHP $version"
    exit 1
  fi
  
  echo ""
done

echo "All tests passed on all PHP versions! 🎉"
