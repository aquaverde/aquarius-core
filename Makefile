COMPOSER=bin/composer.phar
VENDOR=vendor/autoload.php
ADMINER=../dbadmin/index.php

all: $(VENDOR) $(ADMINER)

full: $(VENDOR) $(ADMINER)
	php bin/packer --inline full

bare: $(VENDOR)
	php bin/packer --inline bare

$(VENDOR): $(COMPOSER) composer.json $(wildcard composer.lock)
	php $(COMPOSER) update

$(ADMINER): $(VENDOR)
	sh bin/compile_adminer

$(COMPOSER):
	curl -sS https://getcomposer.org/installer | php -- --install-dir bin



clean:
	rm -rf $(COMPOSER) vendor