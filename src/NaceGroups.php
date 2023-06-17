<?php

namespace Xterr\NaceCodes;

use Symfony\Contracts\Translation\TranslatorInterface;

class NaceGroups extends AbstractDatabase
{
    public function __construct(string $baseDirectory, TranslatorInterface $translator = null)
    {
        parent::__construct($baseDirectory, NaceGroup::class, $translator);
    }

    public function getByCodeAndVersion(string $code, float $version = NaceVersion::VERSION_2): ?NaceGroup
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
