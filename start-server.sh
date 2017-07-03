#!/usr/bin/env bash
php -S [::]:35623 -t build &

sensible-browser [::]:35623
