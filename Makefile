.DEFAULT_GOAL := peppermint
peppermint := peppermint
docs := docs

.PHONY: setupApiDoc peppermint docs

ApiDocPresent := $(shell sh -c apidoc --help 1\>/dev/null)

peppermint:
	@echo [peppermint/build] Rebuilding Pepperminty Wiki
	php build.php

docs: setupApiDoc
	@echo [peppermint/docs] Building docs
	apidoc -vvv -o './RestApiDocs/' -f '.*\.php' -e index.php
	rm -rf doc/

setupApiDoc:
	@echo [peppermint] Checking for apiDoc
ifndef ApiDocPresent
	@echo [peppermint] Attempting to install ApiDoc, since it wasn't detected in your PATH
	@echo [peppermint] Note that you may need to be root, and you'll need npm installed.
	npm install apidoc --global
endif
	@echo [peppermint] Check complete
