#!/usr/bin/env bash
# Print the current version of the project and bump it to the given version.

current_version="$(xmllint --xpath 'string(/module/version)' config.xml)"
echo "Current version: $current_version"

if [[ -z "$1" ]]
then
  echo "To bump the version, provide the new version number as an argument."
  exit 1
fi

# Remove the 'v' prefix if it exists
new_version="${1#v}"

echo "New version: $new_version"

if [ ${#new_version} -gt 8 ]
then
  echo "Please provide a version number with 8 characters or less."
  echo "PrestaShop's database field for the module version only supports up to 8 characters."
  exit 1
fi

if ! [[ "$new_version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]
then
  echo "Invalid version format."
  echo "Examples of a valid version number: 1.2.3"
  exit 1
fi

echo "Bumping version to: $new_version"

perl -pi -e "s/<version><!\[CDATA\[.+\]\]><\/version>/<version><![CDATA[$new_version]]><\/version>/" config.xml
perl -pi -e "s/\$this->version = '.+';$/\$this->version = '$new_version';/" sendynl.php

echo
echo "To release the new version, first, commit the changes:"
echo "  git add --all"
echo "  git commit -m "$new_version""
echo "  git push"
echo
echo "Once the commit is pushed to the master branch, create a new release on GitHub to trigger the build:"
echo "  https://github.com/sendynl/prestashop-module/releases/new?tag=v$new_version"
