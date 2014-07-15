COMPOSER=bin/composer.phar
VENDOR=vendor/autoload.php
ADMINER=../dbadmin/index.php
CKEDITOR_ACCESS=vendor/ckeditor/ckeditor/.htaccess

all: $(VENDOR) $(ADMINER) $(CKEDITOR_ACCESS)

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

$(CKEDITOR_ACCESS): $(VENDOR)
	cp backend/.htaccess $(CKEDITOR_ACCESS)
	
clean:
	rm -rf $(COMPOSER) vendor
	
