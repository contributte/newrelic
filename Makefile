.PHONY: install qa cs csf phpstan tests coverage-clover coverage-html

install:
	composer update

qa: phpstan cs

cs:
ifdef GITHUB_ACTION
	vendor/bin/phpcs --standard=ruleset.xml --report=checkstyle --extensions=php,phpt --tab-width=4 -spq --colors src tests | cs2pr
else
	vendor/bin/phpcs --standard=ruleset.xml --extensions=php,phpt --tab-width=4 -sp --colors src tests
endif

csf:
	vendor/bin/phpcbf --standard=ruleset.xml --extensions=php,phpt --tab-width=4 -sp --colors src tests

phpstan:
	vendor/bin/phpstan analyse -l 8 -c phpstan.neon src

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases

coverage-clover:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.xml --coverage-src ./src tests/Cases

coverage-html:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.html --coverage-src ./src tests/Cases

build:
	docker build -t xnewrelic -f .docker/Dockerfile .

dev: build
	docker run -it --rm -v $(CURDIR):/srv xnewrelic bash
