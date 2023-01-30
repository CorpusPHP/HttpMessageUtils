SRC_FILES = $(shell find src -type f -name '*.php')

README.md: $(SRC_FILES) .mddoc.xml.dist
	vendor/bin/mddoc

.PHONY: fix
fix: cbf
	vendor/bin/php-cs-fixer fix

.PHONY: test
test: cs
	vendor/bin/phpunit
	PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix --dry-run

.PHONY: cs
cs:
	vendor/bin/phpcs

.PHONY: cbf
cbf:
	vendor/bin/phpcbf
