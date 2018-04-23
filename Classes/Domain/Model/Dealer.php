<?php

namespace Pixelant\PxaDealers\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Andriy Oprysko <andriy@pixelant.se>, Pixelant
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Dealer extends AbstractEntity
{

    /**
     * Name of dealer
     *
     * @var \string
     * @validate NotEmpty
     */
    protected $name;

    /**
     * City of dealer
     *
     * @var \string
     * @validate NotEmpty
     */
    protected $city;

    /**
     * E-mail of dealer
     *
     * @var \string
     * @validate NotEmpty
     */
    protected $email;

    /**
     * Telephone
     *
     * @var \string
     */
    protected $phone;

    /**
     * Website url
     *
     * @var \string
     */
    protected $website;

    /**
     * Buy it now url
     *
     * @var \string
     */
    protected $link;

    /**
     * Adrress for google maps
     *
     * @var \string
     * @validate NotEmpty
     */
    protected $address;

    /**
     * Country
     *
     * @var \SJBR\StaticInfoTables\Domain\Model\Country
     */
    protected $country;

    /**
     * Zipcode
     *
     * @var \string
     * @validate NotEmpty
     */
    protected $zipcode;

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
     * Logo
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $logo;

    /**
     * categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaDealers\Domain\Model\Category>
     */
    protected $dealersCategories = null;

    /**
     * showStreetView
     *
     * @var \boolean $showStreetView
     */
    protected $showStreetView = 1;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->dealersCategories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Returns the name
     *
     * @return \string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param \string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the city
     *
     * @return \string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city
     *
     * @param \string $city
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Returns the email
     *
     * @return \string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param \string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Returns the telephone
     *
     * @return \string $telephone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets the telephone
     *
     * @param \string $phone
     * @return void
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Returns the website
     *
     * @return \string $website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the website
     *
     * @param \string $website
     * @return void
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Returns the adrress
     *
     * @return \string $adrress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the adrress
     *
     * @param \string $address
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Returns the country
     *
     * @return \SJBR\StaticInfoTables\Domain\Model\Country $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the country
     *
     * @param \SJBR\StaticInfoTables\Domain\Model\Country $country
     * @return void
     */
    public function setCountry(\SJBR\StaticInfoTables\Domain\Model\Country $country)
    {
        $this->country = $country;
    }

    /**
     * Returns the zipcode
     *
     * @return \string $zipcode
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Sets the zipcode
     *
     * @param \string $zipcode
     * @return void
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * Returns the lat
     *
     * @return float $lat
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Sets the lat
     *
     * @param float $lat
     * @return void
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * Returns the lng
     *
     * @return float $lng
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Sets the lng
     *
     * @param float $lng
     * @return void
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * Adds a category
     *
     * @param \Pixelant\PxaDealers\Domain\Model\Category $category
     * @return void
     */
    public function addCategory(\Pixelant\PxaDealers\Domain\Model\Category $category)
    {
        $this->dealersCategories->attach($category);
    }

    /**
     * Removes a category
     *
     * @param \Pixelant\PxaDealers\Domain\Model\Category $categoryToRemove The category to be removed
     * @return void
     */
    public function removeCategory(\Pixelant\PxaDealers\Domain\Model\Category $categoryToRemove)
    {
        $this->dealersCategories->detach($categoryToRemove);
    }

    /**
     * Returns the categories
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaDealers\Domain\Model\Category> $categories
     */
    public function getDealersCategories()
    {
        return $this->dealersCategories;
    }

    /**
     * Sets the Categories
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaDealers\Domain\Model\Category> $categories
     * @return void
     */
    public function setDealersCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $dealersCategories)
    {
        $this->dealersCategories = $dealersCategories;
    }

    /**
     * Returns the categories as comma separated string
     *
     * @return string $categories_string
     */
    public function getCategoriesString()
    {
        return implode(',', $this->getCategoriesAsUidsArray());
    }

    /**
     * Return categories as array of uids
     *
     * @return array
     */
    public function getCategoriesAsUidsArray()
    {

        $categoriesObjects = $this->getDealersCategories();
        $categories = [];

        /** @var Category $categoryObject */
        foreach ($categoriesObjects as $categoryObject) {
            if (is_object($categoryObject)) {
                $categories[] = $categoryObject->getUid();
            }
        }

        return $categories;
    }

    /**
     * Returns dealer country uid
     *
     * @return integer
     */
    public function getCountryUid()
    {
        return $this->getCountry() !== null ? $this->getCountry()->getUid() : 0;
    }

    /**
     * Returns the showStreetView
     *
     * @return \boolean $showStreetView
     */
    public function getShowStreetView()
    {
        return $this->showStreetView;
    }

    /**
     * Sets the showStreetView
     *
     * @param \boolean $showStreetView
     * @return void
     */
    public function setShowStreetView($showStreetView)
    {
        $this->showStreetView = $showStreetView;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Get array for map view
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'lat' => $this->getLat(),
            'lng' => $this->getLng(),
            'address' => $this->getAddress(),
            'zipcode' => $this->getZipcode(),
            'city' => $this->getCity(),
            'phone' => $this->getPhone(),
            'phoneClear' => str_replace(array(' ', '-'), '', $this->getPhone()),
            'website' => MainUtility::typoLink($this->getWebsite()),
            'link' => MainUtility::typoLink($this->getLink()),
            'email' => MainUtility::typoLink($this->getEmail()),
            'uid' => (string)$this->getUid(),
            'categories' => $this->getDealersCategories(),
            'country' => (string)$this->getCountryUid(),
            'countryName' => $this->getCountry() !== null ? $this->getCountry()->getShortNameLocal() : '',
            'showStreetView' => $this->getShowStreetView(),
            'logo' => $this->getLogo()
        ];
    }
}
