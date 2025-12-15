<?php

declare(strict_types=1);

namespace Xterr\NaceCodes\Translation;

/**
 * Framework-agnostic translator interface.
 *
 * Adapters wrap framework-specific translators to implement this interface.
 * This interface is owned by the library, not by any framework.
 */
interface TranslatorInterface
{
    /**
     * Translates the given message.
     *
     * @param string      $id     The message id (original text as key)
     * @param string|null $locale The locale or null to use the default
     * @param string      $domain The domain for the message
     *
     * @return string The translated string or the original if not found
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'naceCodes'): string;
}
