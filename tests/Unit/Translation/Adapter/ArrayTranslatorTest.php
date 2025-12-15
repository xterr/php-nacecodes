<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Tests\Unit\Translation\Adapter;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\Translation\Adapter\ArrayTranslator;
use Xterr\NaceCodes\Translation\LocaleAwareInterface;
use Xterr\NaceCodes\Translation\TranslatorInterface;

class ArrayTranslatorTest extends TestCase
{
    public function testImplementsTranslatorInterface(): void
    {
        $translator = new ArrayTranslator();

        $this->assertInstanceOf(TranslatorInterface::class, $translator);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        $translator = new ArrayTranslator();

        $this->assertInstanceOf(LocaleAwareInterface::class, $translator);
    }

    public function testDefaultLocaleIsEnglish(): void
    {
        $translator = new ArrayTranslator();

        $this->assertEquals('en', $translator->getLocale());
    }

    public function testSetLocale(): void
    {
        $translator = new ArrayTranslator();
        $translator->setLocale('de');

        $this->assertEquals('de', $translator->getLocale());
    }

    public function testTranslateReturnsOriginalWhenNotFound(): void
    {
        $translator = new ArrayTranslator();
        $result = $translator->translate('Non-existent key');

        $this->assertEquals('Non-existent key', $result);
    }

    public function testTranslateWithGermanLocale(): void
    {
        $translator = new ArrayTranslator(null, 'de');

        $result = $translator->translate('AGRICULTURE, FORESTRY AND FISHING');

        $this->assertEquals('LAND- UND FORSTWIRTSCHAFT, FISCHEREI', $result);
    }

    public function testTranslateWithFrenchLocale(): void
    {
        $translator = new ArrayTranslator(null, 'fr');

        $result = $translator->translate('CONSTRUCTION');

        $this->assertEquals('CONSTRUCTION', $result);
    }

    public function testFallbackLocale(): void
    {
        $translator = new ArrayTranslator(null, 'en', 'de');
        $translator->setFallbackLocale('de');

        $this->assertEquals('de', $translator->getFallbackLocale());
    }

    public function testGetAvailableLocales(): void
    {
        $translator = new ArrayTranslator();
        $locales = $translator->getAvailableLocales();

        $this->assertIsArray($locales);
        $this->assertContains('de', $locales);
        $this->assertContains('fr', $locales);
        $this->assertContains('es', $locales);
    }

    public function testTranslateWithLocaleParameter(): void
    {
        $translator = new ArrayTranslator(null, 'en');

        $result = $translator->translate('AGRICULTURE, FORESTRY AND FISHING', 'de');

        $this->assertEquals('LAND- UND FORSTWIRTSCHAFT, FISCHEREI', $result);
    }

    public function testFallbackToFallbackLocaleWhenTranslationNotFound(): void
    {
        $translator = new ArrayTranslator(null, 'invalid_locale', 'de');

        $result = $translator->translate('AGRICULTURE, FORESTRY AND FISHING');

        $this->assertEquals('LAND- UND FORSTWIRTSCHAFT, FISCHEREI', $result);
    }

    public function testIntegrationWithNaceCodes(): void
    {
        $translator = new ArrayTranslator(null, 'de');
        $factory = new NaceCodesFactory(null, $translator);

        $codes = $factory->getCodes();
        $code = $codes->getByCodeAndVersion('0111', 2);

        $this->assertNotNull($code);
        $this->assertEquals('Growing of cereals (except rice), leguminous crops and oil seeds', $code->getName());
        $this->assertNotEquals($code->getName(), $code->getLocalName());
    }

    public function testIntegrationWithNaceSections(): void
    {
        $translator = new ArrayTranslator(null, 'de');
        $factory = new NaceCodesFactory(null, $translator);

        $sections = $factory->getSections();
        $section = $sections->getByCodeAndVersion('A', 2);

        $this->assertNotNull($section);
        $this->assertEquals('AGRICULTURE, FORESTRY AND FISHING', $section->getName());
        $this->assertEquals('LAND- UND FORSTWIRTSCHAFT, FISCHEREI', $section->getLocalName());
    }

    public function testIntegrationWithNaceDivisions(): void
    {
        $translator = new ArrayTranslator(null, 'de');
        $factory = new NaceCodesFactory(null, $translator);

        $divisions = $factory->getDivisions();
        $division = $divisions->getByCodeAndVersion('01', 2);

        $this->assertNotNull($division);
        $this->assertNotEquals($division->getName(), $division->getLocalName());
    }

    public function testIntegrationWithNaceGroups(): void
    {
        $translator = new ArrayTranslator(null, 'de');
        $factory = new NaceCodesFactory(null, $translator);

        $groups = $factory->getGroups();
        $group = $groups->getByCodeAndVersion('011', 2);

        $this->assertNotNull($group);
        $this->assertNotEquals($group->getName(), $group->getLocalName());
    }

    public function testMultipleLocalesWork(): void
    {
        $locales = ['de', 'fr', 'es', 'it', 'pl'];

        foreach ($locales as $locale) {
            $translator = new ArrayTranslator(null, $locale);
            $factory = new NaceCodesFactory(null, $translator);

            $sections = $factory->getSections();
            $section = $sections->getByCodeAndVersion('A', 2);

            $this->assertNotNull($section, "Section not found for locale: $locale");
            $this->assertNotEmpty($section->getLocalName(), "LocalName empty for locale: $locale");
        }
    }

    public function testCustomLoaderInjection(): void
    {
        $loader = $this->createMock(\Xterr\NaceCodes\Translation\Loader\TranslationLoaderInterface::class);
        $loader->expects($this->once())
            ->method('load')
            ->with('en', 'naceCodes')
            ->willReturn(['test key' => 'test value']);

        $translator = new ArrayTranslator($loader);

        $result = $translator->translate('test key');

        $this->assertEquals('test value', $result);
    }

    public function testCustomBasePath(): void
    {
        $basePath = dirname(__DIR__, 4) . '/Resources/translations/php';
        $translator = new ArrayTranslator(null, 'de', 'en', $basePath);

        $result = $translator->translate('AGRICULTURE, FORESTRY AND FISHING');

        $this->assertEquals('LAND- UND FORSTWIRTSCHAFT, FISCHEREI', $result);
    }

    public function testNoFallbackWhenTargetEqualsAndNotFound(): void
    {
        $translator = new ArrayTranslator(null, 'en', 'en');

        $result = $translator->translate('non-existent-key');

        $this->assertEquals('non-existent-key', $result);
    }

    public function testFallbackNotUsedWhenTargetLocaleHasTranslation(): void
    {
        $translator = new ArrayTranslator(null, 'de', 'fr');

        $result = $translator->translate('AGRICULTURE, FORESTRY AND FISHING');

        $this->assertEquals('LAND- UND FORSTWIRTSCHAFT, FISCHEREI', $result);
    }
}
