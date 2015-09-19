#!/usr/bin/env bash
echo Deleting old index.php
rm build/index.php
php rebuild_module_index.php
php build.php
