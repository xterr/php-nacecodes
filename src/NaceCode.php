<?php

namespace Xterr\NaceCodes;

class NaceCode
{
    /**
     * @var string
     */
    private $section;

    /**
     * @var string
     */
    private $division;

    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $rawCode;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $version = NaceVersion::VERSION_2;

    /**
     * @return string
     */
    public function getSection(): string
    {
        return $this->section;
    }

    /**
     * @return string
     */
    public function getDivision(): string
    {
        return $this->division;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getRawCode(): string
    {
        return $this->rawCode;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getVersion(): float
    {
        return $this->version;
    }
}
