<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Translation\Loader;

/**
 * Loads translations from PHP array files.
 *
 * Zero external dependencies - uses PHP array files directly.
 * This is the native implementation for standalone usage.
 */
final class PhpArrayLoader implements TranslationLoaderInterface
{
    /**
     * @var string|null
     */
    private $basePath;

    /**
     * @var array<string, array<string, string>>
     */
    private $loaded = [];

    /**
     * @var array<string, string[]>
     */
    private $availableLocales = [];

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $locale, string $domain = 'naceCodes'): array
    {
        $cacheKey = $domain . '.' . $locale;

        if (isset($this->loaded[$cacheKey])) {
            return $this->loaded[$cacheKey];
        }

        $file = $this->getFilePath($locale, $domain);

        if (!file_exists($file)) {
            $normalizedLocale = $this->normalizeLocale($locale);
            if ($normalizedLocale !== $locale) {
                $file = $this->getFilePath($normalizedLocale, $domain);
            }
        }

        if (!file_exists($file)) {
            $this->loaded[$cacheKey] = [];
            return $this->loaded[$cacheKey];
        }

        $translations = require $file;

        $this->loaded[$cacheKey] = is_array($translations) ? $translations : [];
        return $this->loaded[$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $locale, string $domain = 'naceCodes'): bool
    {
        return in_array(
            $this->normalizeLocale($locale),
            $this->getAvailableLocales($domain),
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales(string $domain = 'naceCodes'): array
    {
        if (isset($this->availableLocales[$domain])) {
            return $this->availableLocales[$domain];
        }

        $pattern = $this->getBasePath() . '/' . $domain . '.*.php';
        $files = glob($pattern);

        if ($files === false) {
            $files = [];
        }

        $locales = [];
        foreach ($files as $file) {
            $locales[] = $this->extractLocale($file, $domain);
        }

        $this->availableLocales[$domain] = $locales;

        return $this->availableLocales[$domain];
    }

    private function getBasePath(): string
    {
        if ($this->basePath !== null) {
            return $this->basePath;
        }

        return dirname(__DIR__, 3) . '/Resources/translations/php';
    }

    private function getFilePath(string $locale, string $domain): string
    {
        return $this->getBasePath() . '/' . $domain . '.' . $locale . '.php';
    }

    private function normalizeLocale(string $locale): string
    {
        $parts = explode('_', str_replace('-', '_', $locale));
        return strtolower($parts[0]);
    }

    private function extractLocale(string $filePath, string $domain): string
    {
        $filename = basename($filePath, '.php');
        return str_replace($domain . '.', '', $filename);
    }
}
