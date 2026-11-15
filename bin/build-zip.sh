#!/usr/bin/env bash
# Build submission-ready packages in dist/:
#   verdora-<version>.zip            the theme (for WordPress.org), without companion/,
#                                    hidden files (.git…), bin/, dist/ and README.md
#   verdora-companion-<version>.zip  the optional companion plugin
#
# Usage: bash bin/build-zip.sh
#
# @author Priyana Anggiyawan
set -euo pipefail

cd "$(dirname "$0")/.."
root="$(pwd)"
mkdir -p dist

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT

# package <source dir> <slug> <version> [paths to drop…]
package() {
	local src="$1" slug="$2" version="$3"
	shift 3
	local out="$root/dist/${slug}-${version}.zip"
	mkdir -p "$tmp/$slug"
	(cd "$src" && tar --exclude='./.git' -cf - .) | tar -xf - -C "$tmp/$slug"
	find "$tmp/$slug" -name '.*' -prune -exec rm -rf {} +
	for path in "$@"; do rm -rf "${tmp:?}/$slug/$path"; done
	rm -f "$out"
	(cd "$tmp" && zip -rqX "$out" "$slug")
	echo "Built dist/$(basename "$out") ($(du -h "$out" | cut -f1))"
}

theme_version="$(sed -n 's/^Version:[[:space:]]*//p' style.css | tr -d '\r')"
package "$root" verdora "$theme_version" bin dist companion README.md

plugin_dir="$root/companion/verdora-companion"
plugin_version="$(sed -n 's/^ \* Version:[[:space:]]*//p' "$plugin_dir/verdora-companion.php" | tr -d '\r')"
package "$plugin_dir" verdora-companion "$plugin_version"
