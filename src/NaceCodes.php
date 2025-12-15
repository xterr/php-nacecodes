<?php

namespace Xterr\NaceCodes;

use Xterr\NaceCodes\Translation\TranslatorInterface;

class NaceCodes extends AbstractDatabase
{
    /**
     * @param string                   $baseDirectory
     * @param TranslatorInterface|null $translator
     */
    public function __construct(string $baseDirectory, ?TranslatorInterface $translator = null)
    {
        parent::__construct($baseDirectory, NaceCode::class, $translator);
    }

    public function getByCodeAndVersion(string $code, float $version = NaceVersion::VERSION_2): ?NaceCode
    {
        return $this->_find('code', [$code, $version])[0] ?? null;
    }

    public function getByRawCodeAndVersion(string $rawCode, float $version = NaceVersion::VERSION_2): ?NaceCode
    {
        return $this->_find('rawCode', [$rawCode, $version])[0] ?? null;
    }

    public function getAllByVersion(float $version = NaceVersion::VERSION_2): array
    {
        return $this->_find('version', $version);
    }

    protected function _getIndexDefinition(): array
    {
        return [
            'code' => ['code', 'version'],
            'rawCode' => ['rawCode', 'version'],
            'version' => ['version'],
        ];
    }
}
