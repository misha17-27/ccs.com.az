#!/bin/sh
# Writes the site's rules (deploy/htaccess.conf) into <public_html>/.htaccess between
# "# BEGIN CCS" and "# END CCS" and keeps everything else in that file
# (cPanel writes its PHP version handler there). Usage: merge-htaccess.sh /home/user/public_html/
set -e
target="${1%/}/.htaccess"
rules="$(dirname "$0")/htaccess.conf"
tmp="$target.ccs-tmp"
touch "$target"
{
  echo "# BEGIN CCS"
  cat "$rules"
  echo "# END CCS"
  sed '/^# BEGIN CCS$/,/^# END CCS$/d' "$target"
} > "$tmp"
mv "$tmp" "$target"
