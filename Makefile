COMPOSER=bin/composer.phar
VENDOR=vendor/autoload.php

all: $(VENDOR)

full: $(VENDOR)
	php bin/packer full

bare: $(VENDOR)
	php bin/packer core

$(VENDOR): $(COMPOSER) composer.json $(wildcard composer.lock)
	php $(COMPOSER) update

$(COMPOSER):
	curl -sS https://getcomposer.org/installer | php -- --install-dir bin

clean:
	rm -rf $(COMPOSER) vendor