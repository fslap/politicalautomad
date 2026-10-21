#!/bin/bash

# Build an installable zip archive of the current branch,
# e.g. for uploading to shared hosting like lima-city.
#
# The archive has the same structure as the official automad-dist package:
# compiled assets, PHP dependencies, the default theme and the default content.
# Development files like automad/src/client, tests or node_modules are excluded.
#
# Usage: bash bin/dist-zip.sh [output-dir]

set -euo pipefail

dir=$(dirname "$0")
cd "$dir/.."

repoDir=$(pwd)
outDir=$(realpath "${1:-$repoDir/build}")
branch=$(git rev-parse --abbrev-ref HEAD)
commit=$(git rev-parse --short HEAD)
version=$(git describe --tags --abbrev=0 2>/dev/null || echo "dev")
name="politicalautomad-${branch//\//-}-$commit"
workDir=$(mktemp -d)

trap 'rm -rf "$workDir"' EXIT

if [[ $(git status -s --untracked-files=no) ]]; then
	echo "Warning: uncommitted changes are not included, the archive is built from $commit."
fi

echo '---------------------------------------------------------------------------'
echo "Exporting $branch ($commit) ..."
git archive --format=tar HEAD | tar -x -C "$workDir"

cd "$workDir"

echo '---------------------------------------------------------------------------'
echo "Building assets ..."
npm ci --loglevel=error --no-audit --no-fund || npm install --loglevel=error --no-audit --no-fund
npm run build

echo '---------------------------------------------------------------------------'
echo "Installing PHP dependencies ..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --no-interaction --optimize-autoloader
(cd lib && composer install --no-dev --no-interaction --optimize-autoloader)

if [ ! -d packages/automad/standard-lite ] && [ -d vendor/automad/standard-lite ]; then
	# The package installer plugin may be disabled, move the theme manually.
	mkdir -p packages/automad
	cp -R vendor/automad/standard-lite packages/automad/
fi

echo '---------------------------------------------------------------------------'
echo "Assembling dist ..."
distDir="$workDir/dist-package"
mkdir -p "$distDir"

tar -c \
	--exclude=automad/src/client \
	--exclude=automad/tests \
	automad cache config lib packages pages shared vendor \
	.htaccess composer.json composer.lock index.php \
	LICENSE.md LICENSE_PARTY_PURPOSE.md MULTI_LICENSE_PARTY_PURPOSE.md |
	tar -x -C "$distDir"

# Disable the dashboard updater since it would replace this fork with the upstream version.
touch "$distDir/.no-updates"

echo "$version-$commit ($branch)" >"$distDir/VERSION"

mkdir -p "$outDir"
rm -f "$outDir/$name.zip"

(cd "$distDir" && zip -qr -9 "$outDir/$name.zip" . -x '*.DS_Store')

echo '---------------------------------------------------------------------------'
echo "Created $outDir/$name.zip ($(du -h "$outDir/$name.zip" | cut -f1))"
