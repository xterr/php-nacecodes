<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Translation;

/**
 * Interface for translators that support locale management.
 *
 * Separated from TranslatorInterface following Single Responsibility Principle.
 */
interface LocaleAwareInterface
{
    /**
     * Sets the current locale.
     *
     * @param string $locale The locale code (e.g., 'en', 'de', 'fr')
     */
    public function setLocale(string $locale): void;

    /**
     * Gets the current locale.
     *
     * @return string The current locale code
     */
    public function getLocale(): string;

    /**
     * Gets the list of available locales.
     *
     * @return string[] Array of available locale codes
     */
    public function getAvailableLocales(): array;
}
