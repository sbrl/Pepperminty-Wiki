.DEFAULT_GOAL := peppermint

.PHONY: setupApiDoc peppermint docs rest_docs module_api_docs

ApiDocPresent := $(shell sh -c apidoc --help 1\>/dev/null && rm -rf doc/)

peppermint:
	@echo [peppermint/build] Rebuilding Pepperminty Wiki
	php build.php

docs: rest_docs module_api_docs

rest_docs: setupApiDoc
	@echo [peppermint/docs] Building docs
	apidoc -o './docs/RestApi/' --config apidoc.json -f '.*\.php' -e index.php
	rm -rf doc/

module_api_docs: phpdoc
	@echo [peppermint/module api docs] Updating module api docs
	php phpdoc run --directory . --target docs/ModuleApi --cache-folder docs/ModuleApiCache --ignore build/,php_error.php,Parsedown*,*.html --title "Pepperminty Wiki Module API" --visibility public

phpdoc:
	curl -L https://phpdoc.org/phpDocumentor.phar -o phpdoc

setupApiDoc:
	@echo [peppermint] Checking for apiDoc
ifndef ApiDocPresent
	@echo [peppermint] Attempting to install ApiDoc, since it wasn't detected in your PATH
	@echo [peppermint] Note that you may need to be root, and you'll need npm installed.
	npm install apidoc --global
endif
	@echo [peppermint] Check complete
