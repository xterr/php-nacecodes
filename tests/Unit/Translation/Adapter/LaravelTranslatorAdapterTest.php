<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Tests\Unit\Translation\Adapter;

use PHPUnit\Framework\TestCase;
use Xterr\NaceCodes\Translation\Adapter\LaravelTranslatorAdapter;
use Xterr\NaceCodes\Translation\LocaleAwareInterface;
use Xterr\NaceCodes\Translation\TranslatorInterface;

/**
 * @requires function interface_exists
 */
class LaravelTranslatorAdapterTest extends TestCase
{
    /** @var bool */
    private $laravelAvailable;

    protected function setUp(): void
    {
        $this->laravelAvailable = interface_exists('Illuminate\Contracts\Translation\Translator');
    }

    public function testImplementsTranslatorInterface(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $adapter = new LaravelTranslatorAdapter();

        $this->assertInstanceOf(TranslatorInterface::class, $adapter);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $adapter = new LaravelTranslatorAdapter();

        $this->assertInstanceOf(LocaleAwareInterface::class, $adapter);
    }

    public function testTranslateReturnsOriginalWhenNoTranslatorSet(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $adapter = new LaravelTranslatorAdapter();

        $result = $adapter->translate('Some text');

        $this->assertEquals('Some text', $result);
    }

    public function testTranslateDelegatesToLaravelTranslator(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $laravelTranslator = $this->createMock(\Illuminate\Contracts\Translation\Translator::class);
        $laravelTranslator
            ->method('getLocale')
            ->willReturn('en');
        $laravelTranslator
            ->expects($this->once())
            ->method('get')
            ->with('nacecodes::naceCodes.Some text', [], 'en')
            ->willReturn('Translated text');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        $result = $adapter->translate('Some text');

        $this->assertEquals('Translated text', $result);
    }

    public function testTranslateReturnsOriginalWhenKeyNotFound(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $laravelTranslator = $this->createMock(\Illuminate\Contracts\Translation\Translator::class);
        $laravelTranslator
            ->method('getLocale')
            ->willReturn('en');
        $laravelTranslator
            ->method('get')
            ->willReturnArgument(0); // Returns the key itself

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        $result = $adapter->translate('Some text');

        $this->assertEquals('Some text', $result);
    }

    public function testTranslatePassesLocaleToLaravelTranslator(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $laravelTranslator = $this->createMock(\Illuminate\Contracts\Translation\Translator::class);
        $laravelTranslator
            ->expects($this->once())
            ->method('get')
            ->with('nacecodes::naceCodes.Some text', [], 'de')
            ->willReturn('German text');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        $result = $adapter->translate('Some text', 'de');

        $this->assertEquals('German text', $result);
    }

    public function testCustomNamespace(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $laravelTranslator = $this->createMock(\Illuminate\Contracts\Translation\Translator::class);
        $laravelTranslator
            ->method('getLocale')
            ->willReturn('en');
        $laravelTranslator
            ->expects($this->once())
            ->method('get')
            ->with('custom::naceCodes.Some text', [], 'en')
            ->willReturn('Custom namespace text');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator, 'custom');

        $result = $adapter->translate('Some text');

        $this->assertEquals('Custom namespace text', $result);
    }

    public function testSetTranslator(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $laravelTranslator = $this->createMock(\Illuminate\Contracts\Translation\Translator::class);
        $laravelTranslator
            ->method('getLocale')
            ->willReturn('en');
        $laravelTranslator
            ->method('get')
            ->willReturn('Translated');

        $adapter = new LaravelTranslatorAdapter();
        $adapter->setTranslator($laravelTranslator);

        $result = $adapter->translate('Text');

        $this->assertEquals('Translated', $result);
    }

    public function testGetLocaleReturnsEnglishWhenNoTranslatorSet(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $adapter = new LaravelTranslatorAdapter();

        $this->assertEquals('en', $adapter->getLocale());
    }

    public function testGetLocaleDelegatesToLaravelTranslator(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $laravelTranslator = $this->createMock(\Illuminate\Contracts\Translation\Translator::class);
        $laravelTranslator
            ->method('getLocale')
            ->willReturn('de');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        $this->assertEquals('de', $adapter->getLocale());
    }

    public function testSetLocaleDelegatesToLaravelTranslator(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $laravelTranslator = $this->createMock(\Illuminate\Contracts\Translation\Translator::class);
        $laravelTranslator
            ->expects($this->once())
            ->method('setLocale')
            ->with('de');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);
        $adapter->setLocale('de');
    }

    public function testSetLocaleDoesNothingWhenNoTranslatorSet(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $adapter = new LaravelTranslatorAdapter();

        // Should not throw
        $adapter->setLocale('de');
        $this->assertTrue(true);
    }

    public function testGetAvailableLocalesReturnsEmptyArray(): void
    {
        if (!$this->laravelAvailable) {
            $this->markTestSkipped('Laravel contracts not available');
        }

        $adapter = new LaravelTranslatorAdapter();

        $this->assertEquals([], $adapter->getAvailableLocales());
    }
}
