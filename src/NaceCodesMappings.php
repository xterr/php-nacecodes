<?php

namespace Xterr\NaceCodes;

class NaceCodesMappings
{
    /**
     * @var string
     */
    private $baseDirectory;

    private $data = [];

    public function __construct(string $baseDirectory = null)
    {
        $this->baseDirectory = $baseDirectory ?? __DIR__ . '/../Resources';
    }

    public function getMapping(string $from_code, int $from_version): array
    {
        $this->_loadData();

        foreach ($this->data as $mapping) {
            if ($mapping['from_code'] === $from_code && $mapping['from_version'] === $from_version) {
                return [$mapping['to_code'], $mapping['to_version']];
            }
        }

        return [];
    }

    private function _loadData(): void
    {
        if (!empty($this->data)) {
            return;
        }

        $this->data = json_decode(
            file_get_contents(
                $this->baseDirectory . DIRECTORY_SEPARATOR . 'naceCodesMapping.json'
            ),
            true
        );
    }
}
