<?php

namespace Xterr\NaceCodes;

use Symfony\Contracts\Translation\TranslatorInterface;

class NaceCodesFactory
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(string $baseDirectory = null, TranslatorInterface $translator = null)
    {
        $this->baseDirectory = $baseDirectory ?? __DIR__ . '/../Resources';
        $this->translator = $translator;
    }

    public function getCodes(): NaceCodes
    {
        return new NaceCodes($this->baseDirectory, $this->translator);
    }

    public function getSections(): NaceSections
    {
        return new NaceSections($this->baseDirectory, $this->translator);
    }

    public function getDivisions(): NaceDivisions
    {
        return new NaceDivisions($this->baseDirectory, $this->translator);
    }

    public function getGroups(): NaceGroups
    {
        return new NaceGroups($this->baseDirectory, $this->translator);
    }

    public function getMappings(): NaceCodesMappings
    {
        return new NaceCodesMappings($this->baseDirectory);
    }
}
