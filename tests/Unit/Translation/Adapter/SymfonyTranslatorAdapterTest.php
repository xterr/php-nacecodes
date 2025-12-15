<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Tests\Unit\Translation\Adapter;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\LocaleAwareInterface as SymfonyLocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;
use Xterr\NaceCodes\Translation\Adapter\SymfonyTranslatorAdapter;
use Xterr\NaceCodes\Translation\LocaleAwareInterface;
use Xterr\NaceCodes\Translation\TranslatorInterface;

class SymfonyTranslatorAdapterTest extends TestCase
{
    public function testImplementsTranslatorInterface(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        $this->assertInstanceOf(TranslatorInterface::class, $adapter);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        $this->assertInstanceOf(LocaleAwareInterface::class, $adapter);
    }

    public function testTranslateReturnsOriginalWhenNoTranslatorSet(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        $result = $adapter->translate('Some text');

        $this->assertEquals('Some text', $result);
    }

    public function testTranslateDelegatesToSymfonyTranslator(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $symfonyTranslator
            ->expects($this->once())
            ->method('trans')
            ->with('Some text', [], 'naceCodes', null)
            ->willReturn('Translated text');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $result = $adapter->translate('Some text');

        $this->assertEquals('Translated text', $result);
    }

    public function testTranslatePassesLocaleToSymfonyTranslator(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $symfonyTranslator
            ->expects($this->once())
            ->method('trans')
            ->with('Some text', [], 'naceCodes', 'de')
            ->willReturn('German text');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $result = $adapter->translate('Some text', 'de');

        $this->assertEquals('German text', $result);
    }

    public function testTranslatePassesDomainToSymfonyTranslator(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $symfonyTranslator
            ->expects($this->once())
            ->method('trans')
            ->with('Some text', [], 'custom_domain', null)
            ->willReturn('Custom domain text');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $result = $adapter->translate('Some text', null, 'custom_domain');

        $this->assertEquals('Custom domain text', $result);
    }

    public function testSetTranslator(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $symfonyTranslator
            ->method('trans')
            ->willReturn('Translated');

        $adapter = new SymfonyTranslatorAdapter();
        $adapter->setTranslator($symfonyTranslator);

        $result = $adapter->translate('Text');

        $this->assertEquals('Translated', $result);
    }

    public function testGetLocaleReturnsEnglishWhenNoTranslatorSet(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        $this->assertEquals('en', $adapter->getLocale());
    }

    public function testGetLocaleReturnsEnglishWhenTranslatorNotLocaleAware(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $this->assertEquals('en', $adapter->getLocale());
    }

    public function testGetLocaleDelegatesToLocaleAwareTranslator(): void
    {
        $symfonyTranslator = $this->createMock(LocaleAwareSymfonyTranslatorStub::class);
        $symfonyTranslator
            ->method('getLocale')
            ->willReturn('de');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $this->assertEquals('de', $adapter->getLocale());
    }

    public function testSetLocaleDelegatesToLocaleAwareTranslator(): void
    {
        $symfonyTranslator = $this->createMock(LocaleAwareSymfonyTranslatorStub::class);
        $symfonyTranslator
            ->expects($this->once())
            ->method('setLocale')
            ->with('de');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);
        $adapter->setLocale('de');
    }

    public function testSetLocaleDoesNothingWhenTranslatorNotLocaleAware(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        // Should not throw
        $adapter->setLocale('de');
        $this->assertTrue(true);
    }

    public function testGetAvailableLocalesReturnsEmptyArray(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        $this->assertEquals([], $adapter->getAvailableLocales());
    }
}

/**
 * Stub interface combining Symfony's TranslatorInterface and LocaleAwareInterface.
 */
interface LocaleAwareSymfonyTranslatorStub extends SymfonyTranslatorInterface, SymfonyLocaleAwareInterface
{
}
