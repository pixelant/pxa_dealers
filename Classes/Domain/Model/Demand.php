<?php

namespace Pixelant\PxaDealers\Domain\Model;

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
     * @var string
     */
    protected $orderBy = 'crdate';

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @param array $countries
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;
    }

    /**
     * @return string
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    /**
     * @param string $orderDirection
     */
    public function setOrderDirection($orderDirection)
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
     * create deman object
     *
     * @param array $demand
     * @return Demand
     */
    public static function getInstance($demand = [])
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
    protected static function processDemandSettings($settings)
    {
        foreach ($settings as $field => $value) {
            if (GeneralUtility::inList(self::FIELDS_ARRAY, $field)) {
                $settings[$field] = GeneralUtility::intExplode(',', $value, true);
            }
        }

        return $settings;
    }
}
