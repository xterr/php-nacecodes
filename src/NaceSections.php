<?php

namespace Xterr\NaceCodes;

use Xterr\NaceCodes\Translation\TranslatorInterface;

class NaceSections extends AbstractDatabase
{
    /**
     * @param string                   $baseDirectory
     * @param TranslatorInterface|null $translator
     */
    public function __construct(string $baseDirectory, ?TranslatorInterface $translator = null)
    {
        parent::__construct($baseDirectory, NaceSection::class, $translator);
    }

    public function getByCodeAndVersion(string $code, float $version = NaceVersion::VERSION_2): ?NaceSection
    {
        $entries = $this->_find('code', [$code, $version]);

        return $entries[0] ?? null;
    }

    public function getAllByVersion(float $version = NaceVersion::VERSION_2): array
    {
        return $this->_find('version', $version);
    }

    protected function _getIndexDefinition(): array
    {
        return [
            'code' => ['code', 'version'],
            'version' => ['version'],
        ];
    }
}
