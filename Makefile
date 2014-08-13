COMPOSER=bin/composer.phar
VENDOR=vendor/autoload.php
ADMINER=../dbadmin/index.php
CKEDITOR_ACCESS=vendor/ckeditor/ckeditor/.htaccess
REVISION:=$(shell git describe --abbrev=4 --dirty --always)

all: $(VENDOR) $(ADMINER) $(CKEDITOR_ACCESS) revision

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

$(CKEDITOR_ACCESS): $(VENDOR)
	cp backend/.htaccess $(CKEDITOR_ACCESS)
	
clean:
	rm -rf $(COMPOSER) vendor

.PHONY: revision
revision:
	echo $(REVISION) > $@
