<?php

namespace Pixelant\PxaDealers\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Search
 * @package Pixelant\PxaDealers\Domain\Model
 */
class Search extends AbstractEntity
{
    /**
     * Search query string lower case
     *
     * @var string
     */
    protected $searchTermLowercase = '';

    /**
     * Search query string original
     *
     * @var string
     */
    protected $searchTermOriginal = '';

    /**
     * Where to search
     *
     * @var array
     */
    protected $searchFields = [];

    /**
     * Where to search (Used only in ajax suggest)
     *
     * @var string
     */
    protected $pid = 0;

    /**
     * Search in radius
     *
     * @var bool
     */
    protected $searchInRadius = false;

    /**
     * lat
     *
     * @var float
     */
    protected $lat;

    /**
     * lng
     *
     * @var float
     */
    protected $lng;

    /**
     * @var int
     */
    protected $radius = 0;

    /**
     * @return string
     */
    public function getSearchTermLowercase()
    {
        return $this->searchTermLowercase;
    }

    /**
     * @param string $searchTermLowercase
     */
    public function setSearchTermLowercase($searchTermLowercase)
    {
        $this->searchTermLowercase = trim($searchTermLowercase);
    }

    /**
     * @return string
     */
    public function getSearchTermOriginal()
    {
        return $this->searchTermOriginal;
    }

    /**
     * @param string $searchTermOriginal
     */
    public function setSearchTermOriginal($searchTermOriginal)
    {
        $this->searchTermOriginal = $searchTermOriginal;
        $this->setSearchTermLowercase(strtolower($searchTermOriginal));
    }

    /**
     * @return array
     */
    public function getSearchFields()
    {
        return $this->searchFields;
    }

    /**
     * @param array $searchFields
     */
    public function setSearchFields($searchFields)
    {
        $this->searchFields = $searchFields;
    }

    /**
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param string $pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return bool
     */
    public function isSearchInRadius()
    {
        return $this->searchInRadius;
    }

    /**
     * @param bool $searchInRadius
     */
    public function setSearchInRadius($searchInRadius)
    {
        $this->searchInRadius = $searchInRadius;
    }

    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat($lat)
    {
        $this->lat = (float)$lat;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = (float)$lng;
    }

    /**
     * @return int
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * @param int $radius
     */
    public function setRadius($radius)
    {
        $this->radius = (int)$radius;
    }
}
