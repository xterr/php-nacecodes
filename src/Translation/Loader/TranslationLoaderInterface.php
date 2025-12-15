<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Translation\Loader;

/**
 * Interface for loading translation data from various sources.
 */
interface TranslationLoaderInterface
{
    /**
     * Loads translations for a given locale and domain.
     *
     * @param string $locale The locale code (e.g., 'en', 'de')
     * @param string $domain The translation domain
     *
     * @return array<string, string> Key-value pairs of translations
     */
    public function load(string $locale, string $domain): array;

    /**
     * Checks if translations are available for a given locale and domain.
     *
     * @param string $locale The locale code
     * @param string $domain The translation domain
     *
     * @return bool True if translations are available
     */
    public function supports(string $locale, string $domain): bool;

    /**
     * Gets the list of available locales for a domain.
     *
     * @param string $domain The translation domain
     *
     * @return string[] Array of available locale codes
     */
    public function getAvailableLocales(string $domain): array;
}
