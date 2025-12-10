# FlipDev SEO Extension - Makefile
#
# @author     Philipp Breitsprecher <philippbreitsprecher@gmail.com>
# @copyright  Copyright (c) 2025 FlipDev

.PHONY: help test phpstan phpcs phpunit install clean

# Default target
help:
	@echo "FlipDev SEO Extension - Available Commands:"
	@echo ""
	@echo "  make test       - Run all tests (PHPStan + PHPUnit)"
	@echo "  make phpstan    - Run PHPStan static analysis"
	@echo "  make phpcs      - Run PHP CodeSniffer"
	@echo "  make phpunit    - Run PHPUnit tests"
	@echo "  make coverage   - Generate code coverage report"
	@echo "  make install    - Install module in Magento"
	@echo "  make clean      - Clean cache and generated files"
	@echo ""

# Run all tests
test: phpstan phpunit

# Run PHPStan
phpstan:
	@echo "Running PHPStan..."
	@vendor/bin/phpstan analyse --configuration=phpstan.neon || true

# Run PHP CodeSniffer
phpcs:
	@echo "Running PHP CodeSniffer..."
	@vendor/bin/phpcs --standard=Magento2 --extensions=php Block Controller Helper Model Observer Setup || true

# Run PHPUnit tests
phpunit:
	@echo "Running PHPUnit tests..."
	@vendor/bin/phpunit --configuration=phpunit.xml

# Generate coverage report
coverage:
	@echo "Generating code coverage..."
	@vendor/bin/phpunit --configuration=phpunit.xml --coverage-html=dev/tests/unit/coverage/html
	@echo "Coverage report generated in: dev/tests/unit/coverage/html/index.html"

# Install module
install:
	@echo "Installing FlipDev_Seo..."
	@php bin/magento module:enable FlipDev_Seo
	@php bin/magento setup:upgrade
	@php bin/magento setup:di:compile
	@php bin/magento setup:static-content:deploy de_DE en_US -f
	@php bin/magento cache:flush
	@echo "✓ Module installed successfully!"

# Uninstall module
uninstall:
	@echo "Uninstalling FlipDev_Seo..."
	@php bin/magento module:disable FlipDev_Seo
	@php bin/magento setup:upgrade
	@php bin/magento cache:flush
	@echo "✓ Module uninstalled!"

# Clean cache and generated files
clean:
	@echo "Cleaning cache..."
	@php bin/magento cache:clean
	@php bin/magento cache:flush
	@rm -rf var/cache/* var/page_cache/* var/view_preprocessed/* var/generation/*
	@echo "✓ Cache cleaned!"

# Quick reinstall (disable, upgrade, enable, upgrade)
reinstall:
	@echo "Reinstalling module..."
	@php bin/magento module:disable FlipDev_Seo
	@php bin/magento setup:upgrade
	@php bin/magento module:enable FlipDev_Seo
	@php bin/magento setup:upgrade
	@php bin/magento cache:flush
	@echo "✓ Module reinstalled!"
