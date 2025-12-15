<?php

namespace Xterr\NaceCodes;

use Xterr\NaceCodes\Translation\TranslatorInterface;

class NaceDivisions extends AbstractDatabase
{
    /**
     * @param string                   $baseDirectory
     * @param TranslatorInterface|null $translator
     */
    public function __construct(string $baseDirectory, ?TranslatorInterface $translator = null)
    {
        parent::__construct($baseDirectory, NaceDivision::class, $translator);
    }

    public function getByCodeAndVersion(string $code, float $version = NaceVersion::VERSION_2): ?NaceDivision
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
