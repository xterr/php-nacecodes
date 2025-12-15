<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Translation\Adapter;

use Illuminate\Contracts\Translation\Translator as LaravelTranslatorContract;
use Xterr\NaceCodes\Translation\LocaleAwareInterface;
use Xterr\NaceCodes\Translation\TranslatorInterface;

/**
 * Adapter that wraps Laravel's translator to implement the library's interface.
 *
 * Allows seamless integration with Laravel applications while keeping the
 * library decoupled from the framework.
 */
final class LaravelTranslatorAdapter implements TranslatorInterface, LocaleAwareInterface
{
    /**
     * @var LaravelTranslatorContract|null
     */
    private $translator;

    /**
     * @var string
     */
    private $namespace = 'nacecodes';

    public function __construct(?LaravelTranslatorContract $translator = null, ?string $namespace = null)
    {
        $this->translator = $translator;
        if ($namespace !== null) {
            $this->namespace = $namespace;
        }
    }

    public function setTranslator(LaravelTranslatorContract $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'naceCodes'): string
    {
        if ($this->translator === null) {
            return $id;
        }

        $key = $this->namespace . '::' . $domain . '.' . $id;
        $translated = $this->translator->get($key, [], $locale ?? $this->getLocale());

        return $translated === $key ? $id : $translated;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): void
    {
        if ($this->translator !== null) {
            $this->translator->setLocale($locale);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        if ($this->translator !== null) {
            return $this->translator->getLocale();
        }

        return 'en';
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales(): array
    {
        return [];
    }
}
