COMPOSER=bin/composer.phar

all: vendor

full: vendor
	php bin/packer full

bare: vendor
	php bin/packer core

vendor: $(COMPOSER)
	php $(COMPOSER) update

$(COMPOSER):
	curl -sS https://getcomposer.org/installer | php -- --install-dir bin

clean:
	rm -rf $(COMPOSER) vendor