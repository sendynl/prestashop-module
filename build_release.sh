#!/usr/bin/env bash
# Bundle the project into a zip file for release.

composer install --no-dev --no-interaction --optimize-autoloader

# Create git archive with sendynl/ prefix
git archive HEAD --format=zip --prefix=sendynl/ > sendynl.zip

# Create temporary directory to match the prefix structure
mkdir -p temp/sendynl
cp -r vendor temp/sendynl/

# Add vendor directory with proper prefix to the zip
cd temp
zip -ur ../sendynl.zip sendynl/vendor

# Clean up temporary directory
cd ..
rm -rf temp

echo "Release package created: sendynl.zip"
