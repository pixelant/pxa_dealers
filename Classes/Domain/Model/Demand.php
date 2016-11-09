<?php

namespace Pixelant\PxaDealers\Domain\Model;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class Demand
 * @package Pixelant\PxaDealers\Domain\Model
 */
class Demand {

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
    public function getCategories() {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories) {
        $this->categories = $categories;
    }

    /**
     * @return array
     */
    public function getCountries() {
        return $this->countries;
    }

    /**
     * @param array $countries
     */
    public function setCountries($countries) {
        $this->countries = $countries;
    }

    /**
     * @return string
     */
    public function getOrderDirection() {
        return $this->orderDirection;
    }

    /**
     * @param string $orderDirection
     */
    public function setOrderDirection($orderDirection) {
        $this->orderDirection = $orderDirection;
    }

    /**
     * @return string
     */
    public function getOrderBy() {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy($orderBy) {
        $this->orderBy = $orderBy;
    }

    /**
     * create deman object
     *
     * @param array $demand
     * @return Demand
     */
    static public function getInstance($demand = []) {
        /** @var Demand $demanObject */
        $demanObject = GeneralUtility::makeInstance(__CLASS__);
        foreach ($demand as $item => $value) {
            if(ObjectAccess::isPropertySettable($demanObject, $item)) {
                ObjectAccess::setProperty($demanObject, $item, $value);
            }
        }

        return $demanObject;
    }
}