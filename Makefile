COMPOSER=bin/composer.phar
VENDOR=vendor/autoload.php
ADMINER=../dbadmin/index.php

all: $(VENDOR) $(ADMINER)

full: $(VENDOR)
	php bin/packer full

bare: $(VENDOR)
	php bin/packer core

$(VENDOR): $(COMPOSER) composer.json $(wildcard composer.lock)
	php $(COMPOSER) update

$(ADMINER):
	sh bin/compile_adminer

$(COMPOSER):
	curl -sS https://getcomposer.org/installer | php -- --install-dir bin



clean:
	rm -rf $(COMPOSER) vendor