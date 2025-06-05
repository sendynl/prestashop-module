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

if ! [[ "$new_version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]
then
  echo "Invalid version format. Please use semantic versioning (https://semver.org/)."
  exit 1
fi

echo "Bumping version to: $new_version"

perl -pi -e "s/<version><!\[CDATA\[.+\]\]><\/version>/<version><![CDATA[$new_version]]><\/version>/" config.xml
perl -pi -e "s/\$this->version = '.+';$/\$this->version = '$new_version';/" sendy.php
