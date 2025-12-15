<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Tests\Unit\Translation\Loader;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\Translation\Loader\PhpArrayLoader;
use Xterr\NaceCodes\Translation\Loader\TranslationLoaderInterface;

class PhpArrayLoaderTest extends TestCase
{
    public function testImplementsTranslationLoaderInterface(): void
    {
        $loader = new PhpArrayLoader();

        $this->assertInstanceOf(TranslationLoaderInterface::class, $loader);
    }

    public function testLoadReturnsArrayForValidLocale(): void
    {
        $loader = new PhpArrayLoader();
        $translations = $loader->load('de', 'naceCodes');

        $this->assertIsArray($translations);
        $this->assertNotEmpty($translations);
    }

    public function testLoadReturnsEmptyArrayForInvalidLocale(): void
    {
        $loader = new PhpArrayLoader();
        $translations = $loader->load('invalid_locale', 'naceCodes');

        $this->assertIsArray($translations);
        $this->assertEmpty($translations);
    }

    public function testSupportsReturnsTrueForValidLocale(): void
    {
        $loader = new PhpArrayLoader();

        $this->assertTrue($loader->supports('de', 'naceCodes'));
        $this->assertTrue($loader->supports('fr', 'naceCodes'));
    }

    public function testSupportsReturnsFalseForInvalidLocale(): void
    {
        $loader = new PhpArrayLoader();

        $this->assertFalse($loader->supports('invalid_locale', 'naceCodes'));
    }

    public function testGetAvailableLocales(): void
    {
        $loader = new PhpArrayLoader();
        $locales = $loader->getAvailableLocales('naceCodes');

        $this->assertIsArray($locales);
        $this->assertContains('de', $locales);
        $this->assertContains('fr', $locales);
        $this->assertContains('es', $locales);
    }

    public function testLoadCachesResults(): void
    {
        $loader = new PhpArrayLoader();

        $first = $loader->load('de', 'naceCodes');
        $second = $loader->load('de', 'naceCodes');

        $this->assertSame($first, $second);
    }

    public function testNormalizesLocale(): void
    {
        $loader = new PhpArrayLoader();

        $this->assertTrue($loader->supports('DE', 'naceCodes'));
        $this->assertTrue($loader->supports('de_DE', 'naceCodes'));
    }

    public function testCustomBasePath(): void
    {
        $basePath = dirname(__DIR__, 4) . '/Resources/translations/php';
        $loader = new PhpArrayLoader($basePath);

        $translations = $loader->load('de', 'naceCodes');

        $this->assertIsArray($translations);
        $this->assertNotEmpty($translations);
    }

    public function testLoadWithNormalizedLocale(): void
    {
        $loader = new PhpArrayLoader();

        $translations = $loader->load('DE', 'naceCodes');

        $this->assertIsArray($translations);
        $this->assertNotEmpty($translations);
    }

    public function testLoadWithRegionLocale(): void
    {
        $loader = new PhpArrayLoader();

        $translations = $loader->load('de_DE', 'naceCodes');

        $this->assertIsArray($translations);
        $this->assertNotEmpty($translations);
    }

    public function testLoadWithHyphenatedLocale(): void
    {
        $loader = new PhpArrayLoader();

        $translations = $loader->load('de-DE', 'naceCodes');

        $this->assertIsArray($translations);
        $this->assertNotEmpty($translations);
    }

    public function testGetAvailableLocalesCachesResults(): void
    {
        $loader = new PhpArrayLoader();

        $first = $loader->getAvailableLocales('naceCodes');
        $second = $loader->getAvailableLocales('naceCodes');

        $this->assertSame($first, $second);
    }

    public function testGetAvailableLocalesReturnsEmptyForInvalidDomain(): void
    {
        $loader = new PhpArrayLoader();

        $locales = $loader->getAvailableLocales('nonexistent_domain');

        $this->assertIsArray($locales);
        $this->assertEmpty($locales);
    }

    public function testLoadReturnsEmptyForInvalidDomain(): void
    {
        $loader = new PhpArrayLoader();

        $translations = $loader->load('de', 'nonexistent_domain');

        $this->assertIsArray($translations);
        $this->assertEmpty($translations);
    }
}
