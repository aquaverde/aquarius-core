COMPOSER=bin/composer.phar
VENDOR=vendor/autoload.php
ADMINER=../dbadmin/index.php
REVISION:=$(shell git describe --abbrev=4 --dirty --always)

all: $(VENDOR) $(ADMINER) revision

full: $(VENDOR) $(ADMINER)
	php bin/packer --inline full

bare: $(VENDOR)
	php bin/packer --inline bare

$(VENDOR): $(COMPOSER) composer.json $(wildcard composer.lock)
	php $(COMPOSER) install

$(ADMINER): $(VENDOR)
	sh bin/compile_adminer

$(COMPOSER):
	curl -sS https://getcomposer.org/installer | php -- --install-dir bin

clean:
	rm -rf $(COMPOSER) vendor

.PHONY: revision
revision:
	echo $(REVISION) > $@
