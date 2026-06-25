#!/usr/bin/env bash
# Acceptance helper: render the shortcode via WP-CLI and assert cards exist.
set -euo pipefail

WP_CLI="php -c C:\\Users\\Usuario\\AppData\\Local\\Temp\\opencode\\php-cli.ini C:\\Users\\Usuario\\AppData\\Local\\Temp\\opencode\\wp-cli.phar --path=app/public"

OUT=$($WP_CLI eval "echo do_shortcode('[formaciones_landing]');")

echo "$OUT" | grep -q 'enyf-card' && echo "PASS: enyf-card found" || (echo "FAIL: enyf-card not found"; exit 1)
