SRC_FILES = $(shell find src -type f -name '*.php')

README.md: $(SRC_FILES) composer.json .mddoc.xml.dist
	vendor/bin/mddoc

.PHONY: fix
fix: cbf
	vendor/bin/php-cs-fixer fix

.PHONY: test
test: cs
	vendor/bin/phpunit
	vendor/bin/php-cs-fixer fix --dry-run
	vendor/bin/phpstan

.PHONY: cs
cs:
	vendor/bin/phpcs

.PHONY: cbf
cbf:
	vendor/bin/phpcbf
