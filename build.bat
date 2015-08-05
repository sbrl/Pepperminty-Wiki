@echo off
echo Deleting old index.php
del build\index.php
php rebuild_module_index.php
php build.php 
