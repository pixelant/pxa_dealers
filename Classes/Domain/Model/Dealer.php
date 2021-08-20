<?php

declare(strict_types=1);

namespace Pixelant\PxaDealers\Domain\Model;

/*
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
 */

use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Dealer extends AbstractEntity implements \JsonSerializable
{
    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher = null;

    /**
     * Name of dealer.
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $name = '';

    /**
     * City of dealer.
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $city = '';

    /**
     * E-mail of dealer.
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $email = '';

    /**
     * Telephone.
     *
     * @var string
     */
    protected $phone = '';

    /**
     * Website url.
     *
     * @var string
     */
    protected $website = '';

    /**
     * Buy it now url.
     *
     * @var string
     */
    protected $link = '';

    /**
     * Address for google maps.
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $address = '';

    /**
     * Country.
     *
     * @var \SJBR\StaticInfoTables\Domain\Model\Country
     */
    protected $country;

    /**
     * Zipcode.
     *
     * @var string
     * @Extbase\Validate("NotEmpty")
     */
    protected $zipcode = '';

    /**
     * lat.
     *
     * @var float
     */
    protected $lat = 0.0;

    /**
     * lng.
     *
     * @var float
     */
    protected $lng = 0.0;

    /**
     * Logo.
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $logo = null;

    /**
     * categories.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaDealers\Domain\Model\Category>
     */
    protected $categories = null;

    /**
     * showStreetView.
     *
     * @var bool
     */
    protected $showStreetView = true;

    /**
     * __construct.
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function injectSignalSlotDispatcher(Dispatcher $dispatcher): void
    {
        $this->signalSlotDispatcher = $dispatcher;
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead.
     *
     * @return void
     */
    protected function initStorageObjects(): void
    {
        $this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return \SJBR\StaticInfoTables\Domain\Model\Country|null
     */
    public function getCountry(): ?\SJBR\StaticInfoTables\Domain\Model\Country
    {
        return $this->country;
    }

    /**
     * @param \SJBR\StaticInfoTables\Domain\Model\Country $country
     */
    public function setCountry(\SJBR\StaticInfoTables\Domain\Model\Country $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     */
    public function setZipcode(string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     */
    public function setLat(float $lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return float
     */
    public function getLng(): float
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     */
    public function setLng(float $lng): void
    {
        $this->lng = $lng;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getCategories(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
     */
    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference|null
     */
    public function getLogo(): ?\TYPO3\CMS\Extbase\Domain\Model\FileReference
    {
        return $this->logo;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $logo
     */
    public function setLogo(\TYPO3\CMS\Extbase\Domain\Model\FileReference $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @return bool
     */
    public function isShowStreetView(): bool
    {
        return $this->showStreetView;
    }

    /**
     * @param bool $showStreetView
     */
    public function setShowStreetView(bool $showStreetView): void
    {
        $this->showStreetView = $showStreetView;
    }

    /**
     * Returns the categories as comma separated string.
     *
     * @return string $categories_string
     */
    public function getCategoriesUidList(): string
    {
        return implode(',', $this->getCategoriesAsUidsArray());
    }

    /**
     * Return categories as array of uids.
     *
     * @return array
     */
    public function getCategoriesAsUidsArray(): array
    {
        return array_map(
            function ($category) {
                return $category->getUid();
            },
            $this->getCategories()->toArray()
        );
    }

    /**
     * Returns dealer country uid.
     *
     * @return int
     */
    public function getCountryUid(): int
    {
        return $this->getCountry() !== null ? $this->getCountry()->getUid() : 0;
    }

    /**
     * Json encode object.
     * @return array
     */
    public function jsonSerialize()
    {
        $dealerData = [
            'name' => $this->getName(),
            'lat' => $this->getLat(),
            'lng' => $this->getLng(),
            'address' => $this->getAddress(),
            'zipcode' => $this->getZipcode(),
            'city' => $this->getCity(),
            'phone' => $this->getPhone(),
            'phoneClear' => str_replace([' ', '-'], '', $this->getPhone()),
            'website' => MainUtility::typoLink($this->getWebsite()),
            'link' => MainUtility::typoLink($this->getLink()),
            'email' => MainUtility::typoLink($this->getEmail()),
            'uid' => (string)$this->getUid(),
            'categories' => $this->getCategories(),
            'country' => (string)$this->getCountryUid(),
            'countryName' => $this->getCountry() !== null ? $this->getCountry()->getShortNameEn() : '',
            'showStreetView' => $this->isShowStreetView(),
            'logo' => $this->getLogo(),
        ];

        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'AfterGeneration', [&$dealerData, $this]);

        return $dealerData;
    }
}
