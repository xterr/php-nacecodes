<?php

namespace Xterr\NaceCodes;

use Xterr\NaceCodes\Translation\TranslatorInterface;

class NaceCodesFactory
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var TranslatorInterface|null
     */
    private $translator;

    /**
     * @param string|null              $baseDirectory Base directory for data files
     * @param TranslatorInterface|null $translator    Translator instance (optional)
     */
    public function __construct(?string $baseDirectory = null, ?TranslatorInterface $translator = null)
    {
        $this->baseDirectory = $baseDirectory ?? __DIR__ . '/../Resources';
        $this->translator = $translator;
    }

    /**
     * @return NaceCodes
     */
    public function getCodes()
    {
        return new NaceCodes($this->baseDirectory, $this->translator);
    }

    /**
     * @return NaceSections
     */
    public function getSections()
    {
        return new NaceSections($this->baseDirectory, $this->translator);
    }

    /**
     * @return NaceDivisions
     */
    public function getDivisions()
    {
        return new NaceDivisions($this->baseDirectory, $this->translator);
    }

    /**
     * @return NaceGroups
     */
    public function getGroups()
    {
        return new NaceGroups($this->baseDirectory, $this->translator);
    }

    /**
     * @return NaceCodesMappings
     */
    public function getMappings()
    {
        return new NaceCodesMappings($this->baseDirectory);
    }
}
