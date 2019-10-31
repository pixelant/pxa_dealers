<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Domain\Model\DTO;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class Demand
 * @package Pixelant\PxaDealers\Domain\Model
 */
class Demand
{
    /**
     * fields from flexform conver to array
     */
    const FIELDS_ARRAY = 'countries,categories';

    /**
     * filter by categories
     *
     * @var array
     */
    protected $categories = [];

    /**
     * countries
     *
     * @var array
     */
    protected $countries = [];

    /**
     * @var string
     */
    protected $orderDirection = QueryInterface::ORDER_DESCENDING;

    /**
     * Search requirements
     *
     * @var Search
     */
    protected $search = null;

    /**
     * @var string
     */
    protected $orderBy = 'crdate';

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @param array $countries
     */
    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }

    /**
     * @return string
     */
    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    /**
     * @param string $orderDirection
     */
    public function setOrderDirection(string $orderDirection): void
    {
        $this->orderDirection = $orderDirection;
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return Search|null
     */
    public function getSearch(): ?Search
    {
        return $this->search;
    }

    /**
     * @param Search $search
     */
    public function setSearch(Search $search): void
    {
        $this->search = $search;
    }

    /**
     * create deman object
     *
     * @param array $demand
     * @return Demand
     */
    public static function getInstance($demand = []): self
    {
        /** @var Demand $demandObject */
        $demandObject = GeneralUtility::makeInstance(__CLASS__);
        foreach (self::processDemandSettings($demand) as $item => $value) {
            if (ObjectAccess::isPropertySettable($demandObject, $item)) {
                ObjectAccess::setProperty($demandObject, $item, $value);
            }
        }

        return $demandObject;
    }

    /**
     * @param $settings
     * @return array
     */
    protected static function processDemandSettings($settings): array
    {
        foreach ($settings as $field => $value) {
            if (GeneralUtility::inList(self::FIELDS_ARRAY, $field)) {
                $settings[$field] = GeneralUtility::intExplode(',', $value, true);
            }
        }

        return $settings;
    }
}
