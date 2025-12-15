<?php

namespace Xterr\NaceCodes;

class NaceDivision
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $rawCode;

    /**
     * @var float
     */
    private $version = NaceVersion::VERSION_2;

    /**
     * @var string
     */
    private $section;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $localName;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRawCode(): string
    {
        return $this->rawCode;
    }

    public function getVersion(): float
    {
        return $this->version;
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the translated name, or falls back to the English name.
     *
     * @return string
     */
    public function getLocalName(): string
    {
        return $this->localName ?? $this->name;
    }
}
