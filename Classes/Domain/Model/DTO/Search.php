<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Domain\Model\DTO;

/**
 * Class Search
 * @package Pixelant\PxaDealers\Domain\Model
 */
class Search
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
    protected $lat = null;

    /**
     * lng
     *
     * @var float
     */
    protected $lng = null;

    /**
     * @var int
     */
    protected $radius = 0;

    /**
     * @return string
     */
    public function getSearchTermLowercase(): string
    {
        return $this->searchTermLowercase;
    }

    /**
     * @param string $searchTermLowercase
     */
    public function setSearchTermLowercase(string $searchTermLowercase): void
    {
        $this->searchTermLowercase = $searchTermLowercase;
    }

    /**
     * @return string
     */
    public function getSearchTermOriginal(): string
    {
        return $this->searchTermOriginal;
    }

    /**
     * @param string $searchTermOriginal
     */
    public function setSearchTermOriginal(string $searchTermOriginal): void
    {
        $this->searchTermOriginal = $searchTermOriginal;
        $this->setSearchTermLowercase(strtolower($searchTermOriginal));
    }

    /**
     * @return array
     */
    public function getSearchFields(): array
    {
        return $this->searchFields;
    }

    /**
     * @param array $searchFields
     */
    public function setSearchFields(array $searchFields): void
    {
        $this->searchFields = $searchFields;
    }

    /**
     * @return string
     */
    public function getPid(): string
    {
        return $this->pid;
    }

    /**
     * @param string $pid
     */
    public function setPid(string $pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return bool
     */
    public function isSearchInRadius(): bool
    {
        return $this->searchInRadius;
    }

    /**
     * @param bool $searchInRadius
     */
    public function setSearchInRadius(bool $searchInRadius): void
    {
        $this->searchInRadius = $searchInRadius;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat(?float $lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return float|null
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng(?float $lng): void
    {
        $this->lng = $lng;
    }

    /**
     * @return int
     */
    public function getRadius(): int
    {
        return $this->radius;
    }

    /**
     * @param int $radius
     */
    public function setRadius(int $radius): void
    {
        $this->radius = $radius;
    }
}
