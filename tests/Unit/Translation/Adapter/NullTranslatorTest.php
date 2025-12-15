<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Tests\Unit\Translation\Adapter;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\NaceCodesFactory;
use Xterr\NaceCodes\Translation\Adapter\NullTranslator;
use Xterr\NaceCodes\Translation\LocaleAwareInterface;
use Xterr\NaceCodes\Translation\TranslatorInterface;

class NullTranslatorTest extends TestCase
{
    public function testImplementsTranslatorInterface(): void
    {
        $translator = new NullTranslator();

        $this->assertInstanceOf(TranslatorInterface::class, $translator);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        $translator = new NullTranslator();

        $this->assertInstanceOf(LocaleAwareInterface::class, $translator);
    }

    public function testTranslateReturnsOriginalText(): void
    {
        $translator = new NullTranslator();

        $result = $translator->translate('Some text to translate');

        $this->assertEquals('Some text to translate', $result);
    }

    public function testTranslateReturnsOriginalTextRegardlessOfLocale(): void
    {
        $translator = new NullTranslator();

        $result = $translator->translate('Some text', 'de');

        $this->assertEquals('Some text', $result);
    }

    public function testTranslateReturnsOriginalTextRegardlessOfDomain(): void
    {
        $translator = new NullTranslator();

        $result = $translator->translate('Some text', null, 'custom_domain');

        $this->assertEquals('Some text', $result);
    }

    public function testDefaultLocaleIsEnglish(): void
    {
        $translator = new NullTranslator();

        $this->assertEquals('en', $translator->getLocale());
    }

    public function testSetLocale(): void
    {
        $translator = new NullTranslator();
        $translator->setLocale('de');

        $this->assertEquals('de', $translator->getLocale());
    }

    public function testGetAvailableLocales(): void
    {
        $translator = new NullTranslator();

        $this->assertEquals(['en'], $translator->getAvailableLocales());
    }

    public function testWithoutTranslatorLocalNameFallsBackToName(): void
    {
        $factory = new NaceCodesFactory();

        $sections = $factory->getSections();
        $section = $sections->getByCodeAndVersion('A', 2);

        $this->assertNotNull($section);
        $this->assertEquals($section->getName(), $section->getLocalName());
    }

    public function testNullTranslatorDoesNotTranslate(): void
    {
        $translator = new NullTranslator();
        $factory = new NaceCodesFactory(null, $translator);

        $sections = $factory->getSections();
        $section = $sections->getByCodeAndVersion('A', 2);

        $this->assertNotNull($section);
        $this->assertEquals($section->getName(), $section->getLocalName());
    }
}
