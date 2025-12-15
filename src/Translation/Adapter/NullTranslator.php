<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Translation\Adapter;

use Xterr\NaceCodes\Translation\LocaleAwareInterface;
use Xterr\NaceCodes\Translation\TranslatorInterface;

/**
 * Null translator that returns the original text unchanged.
 *
 * This is the default translator used when no translation is needed
 * or when running in a context where translations are not available.
 */
final class NullTranslator implements TranslatorInterface, LocaleAwareInterface
{
    /**
     * @var string
     */
    private $locale = 'en';

    /**
     * {@inheritdoc}
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'naceCodes'): string
    {
        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales(): array
    {
        return ['en'];
    }
}
