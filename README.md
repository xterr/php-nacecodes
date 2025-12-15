# PHP NACE Codes

[![PHP Version](https://img.shields.io/badge/PHP-7.1%20|%207.2%20|%207.3%20|%207.4%20|%208.0%20|%208.1%20|%208.2%20|%208.3%20|%208.4-8892BF?logo=php)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![CI](https://github.com/xterr/php-nacecodes/actions/workflows/ci.yml/badge.svg)](https://github.com/xterr/php-nacecodes/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/xterr/php-nacecodes.svg)](https://packagist.org/packages/xterr/php-nacecodes)

A PHP library for working with NACE (Nomenclature of Economic Activities) codes used by the European Union for classifying business activities.

## Features

- Complete NACE Rev. 2 code database
- Hierarchical structure support (Sections, Divisions, Groups, Codes)
- **Multi-language translations** (25 languages included)
- Framework-agnostic translation system with adapters for:
  - Standalone PHP (zero dependencies)
  - Symfony
  - Laravel
- PHP 7.1 - 8.4 support

## Installation

```bash
composer require xterr/php-nacecodes
```

## Quick Start

### Basic Usage (Without Translations)

```php
use Xterr\NaceCodes\NaceCodesFactory;

$factory = new NaceCodesFactory();

// Get all NACE codes
$codes = $factory->getCodes();

// Find a specific code
$code = $codes->getByCodeAndVersion('6201', 2);
echo $code->getName(); // "Computer programming activities"
echo $code->getCode(); // "6201"

// Get sections, divisions, groups
$sections = $factory->getSections();
$section = $sections->getByCodeAndVersion('J', 2);
echo $section->getName(); // "INFORMATION AND COMMUNICATION"

$divisions = $factory->getDivisions();
$groups = $factory->getGroups();
```

### With Translations (Standalone PHP)

The library includes a zero-dependency translator for standalone usage:

```php
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\Translation\Adapter\ArrayTranslator;

// Create translator with German locale
$translator = new ArrayTranslator(null, 'de');

$factory = new NaceCodesFactory(null, $translator);

$sections = $factory->getSections();
$section = $sections->getByCodeAndVersion('A', 2);

echo $section->getName();      // "AGRICULTURE, FORESTRY AND FISHING" (original)
echo $section->getLocalName(); // "LAND- UND FORSTWIRTSCHAFT, FISCHEREI" (translated)

// Change locale at runtime
$translator->setLocale('fr');

// Get available locales
$locales = $translator->getAvailableLocales();
// ['bg', 'cs', 'da', 'de', 'el', 'es', 'et', 'fi', 'fr', 'hr', 'hu', 'it', 'lt', 'lv', 'mt', 'nl', 'no', 'pl', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'tr']
```

### With Symfony Translator

```php
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\Translation\Adapter\SymfonyTranslatorAdapter;
use Symfony\Component\Translation\Translator;

// Your existing Symfony translator
$symfonyTranslator = new Translator('de');
// ... configure loaders and resources

$adapter = new SymfonyTranslatorAdapter($symfonyTranslator);
$factory = new NaceCodesFactory(null, $adapter);

$code = $factory->getCodes()->getByCodeAndVersion('0111', 2);
echo $code->getLocalName(); // German translation
```

### With Laravel Translator

```php
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\Translation\Adapter\LaravelTranslatorAdapter;

// In a Laravel application
$adapter = new LaravelTranslatorAdapter(app('translator'));
$factory = new NaceCodesFactory(null, $adapter);

$code = $factory->getCodes()->getByCodeAndVersion('0111', 2);
echo $code->getLocalName(); // Translated based on Laravel's locale
```

## Available Languages

The library includes translations for 25 languages:

| Code | Language | Code | Language | Code | Language |
|------|----------|------|----------|------|----------|
| bg | Bulgarian | hr | Croatian | pl | Polish |
| cs | Czech | hu | Hungarian | pt | Portuguese |
| da | Danish | it | Italian | ro | Romanian |
| de | German | lt | Lithuanian | ru | Russian |
| el | Greek | lv | Latvian | sk | Slovak |
| es | Spanish | mt | Maltese | sl | Slovenian |
| et | Estonian | nl | Dutch | sv | Swedish |
| fi | Finnish | no | Norwegian | tr | Turkish |
| fr | French | | | | |

## API Reference

### NaceCodesFactory

```php
$factory = new NaceCodesFactory(?string $baseDirectory = null, ?TranslatorInterface $translator = null);

$factory->getCodes();      // Returns NaceCodes
$factory->getSections();   // Returns NaceSections
$factory->getDivisions();  // Returns NaceDivisions
$factory->getGroups();     // Returns NaceGroups
$factory->getMappings();   // Returns NaceCodesMappings
```

### Entity Methods

All entities (NaceCode, NaceSection, NaceDivision, NaceGroup) share these methods:

```php
$entity->getCode();      // NACE code (e.g., "6201", "J", "62", "620")
$entity->getName();      // Original English name
$entity->getLocalName(); // Translated name (falls back to getName() if no translation)
$entity->getVersion();   // NACE version (e.g., 2 for Rev. 2)
```

### Repository Methods

```php
// Get by code and version
$codes->getByCodeAndVersion('6201', 2);
$sections->getByCodeAndVersion('J', 2);
$divisions->getByCodeAndVersion('62', 2);
$groups->getByCodeAndVersion('620', 2);

// Get all by version
$codes->getAllByVersion(2);

// Iteration
foreach ($codes as $code) {
    echo $code->getName();
}

// Count
echo count($codes);
```

### Translator Configuration

```php
use Xterr\NaceCodes\Translation\Adapter\ArrayTranslator;

$translator = new ArrayTranslator(
    ?TranslationLoaderInterface $loader = null,  // Custom loader (optional)
    ?string $defaultLocale = 'en',               // Default locale
    ?string $fallbackLocale = 'en',              // Fallback when translation not found
    ?string $basePath = null                     // Custom translations path
);

$translator->setLocale('de');
$translator->getLocale();              // 'de'
$translator->setFallbackLocale('en');
$translator->getFallbackLocale();      // 'en'
$translator->getAvailableLocales();    // ['bg', 'cs', ...]
```

## Testing

```bash
composer install
./vendor/bin/phpunit
```

## Requirements

- PHP 7.1 or higher
- ext-json

## License

MIT License. See [LICENSE](LICENSE) for details.

## Author

Razvan Ceana - [razvan@ceana.ro](mailto:razvan@ceana.ro)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
