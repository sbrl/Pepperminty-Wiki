#!/bin/sh

# Output version numbers of stuff
echo ">>> php-fpm84 -v";
php-fpm84 -v
echo ">>> caddy --version";
caddy --version

echo ">>> DEBUG:php-fpm peppermint conf";
cat /etc/php84/php-fpm.d/peppermint.conf

# Start php-fpm
echo ">>> php-fpm84 -F &";
php-fpm84 -F &

# Empirical testing reveals that this *should* keep both running
echo ">>> exec caddy run --config /etc/caddy/Caddyfile --adapter caddyfile";
exec caddy run --config /etc/caddy/Caddyfile --adapter caddyfile

