<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Translation\Adapter;

use Symfony\Contracts\Translation\LocaleAwareInterface as SymfonyLocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;
use Xterr\NaceCodes\Translation\LocaleAwareInterface;
use Xterr\NaceCodes\Translation\TranslatorInterface;

/**
 * Adapter that wraps Symfony's translator to implement the library's interface.
 *
 * Allows seamless integration with Symfony applications while keeping the
 * library decoupled from the framework.
 */
final class SymfonyTranslatorAdapter implements TranslatorInterface, LocaleAwareInterface
{
    /**
     * @var SymfonyTranslatorInterface|null
     */
    private $translator;

    public function __construct(?SymfonyTranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    public function setTranslator(SymfonyTranslatorInterface $translator): void
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

        return $this->translator->trans($id, [], $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): void
    {
        if ($this->translator instanceof SymfonyLocaleAwareInterface) {
            $this->translator->setLocale($locale);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        if ($this->translator instanceof SymfonyLocaleAwareInterface) {
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
