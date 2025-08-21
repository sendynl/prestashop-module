#!/usr/bin/env bash
# Bundle the project into a zip file for release.

composer install --no-dev --optimize-autoloader

# Create git archive with sendy/ prefix
git archive HEAD --format=zip --prefix=sendy/ > sendy.zip

# Create temporary directory to match the prefix structure
mkdir -p temp/sendy
cp -r vendor temp/sendy/

# Add vendor directory with proper prefix to the zip
cd temp
zip -ur ../sendy.zip sendy/vendor

# Clean up temporary directory
cd ..
rm -rf temp
