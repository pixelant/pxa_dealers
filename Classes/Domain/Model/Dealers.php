<?php
namespace PXA\PxaDealers\Domain\Model;

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

/**
 *
 *
 * @package pxa_purus_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Dealers extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

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
	protected $telephone;

	/**
	 * Website url
	 *
	 * @var \string
	 */
	protected $website;

	/**
	 * Adrress for google maps
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $adrress;

	/**
	 * Country
	 *
	 * @var \string
	 * @validate NotEmpty
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
	 * ZipcodeSearch
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $zipcodeSearch;

	/**
	 * lat
	 *
	 * @var \string
	 */
	protected $lat;

	/**
	 * Description
	 *
	 * @var \string
	 */
	protected $lng;

	/**
	 * latLngIsSet
	 *
	 * @var boolean
	 */
	protected $latLngIsSet = FALSE;


	/**
	 * Returns the name
	 *
	 * @return \string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param \string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the city
	 *
	 * @return \string $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Sets the city
	 *
	 * @param \string $city
	 * @return void
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * Returns the email
	 *
	 * @return \string $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the email
	 *
	 * @param \string $email
	 * @return void
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Returns the telephone
	 *
	 * @return \string $telephone
	 */
	public function getTelephone() {
		return $this->telephone;
	}

	/**
	 * Sets the telephone
	 *
	 * @param \string $telephone
	 * @return void
	 */
	public function setTelephone($telephone) {
		$this->telephone = $telephone;
	}

	/**
	 * Returns the website
	 *
	 * @return \string $website
	 */
	public function getWebsite() {
		return $this->website;
	}

	/**
	 * Sets the website
	 *
	 * @param \string $website
	 * @return void
	 */
	public function setWebsite($website) {
		$this->website = $website;
	}

	/**
	 * Returns the adrress
	 *
	 * @return \string $adrress
	 */
	public function getAdrress() {
		return $this->adrress;
	}

	/**
	 * Sets the adrress
	 *
	 * @param \string $adrress
	 * @return void
	 */
	public function setAdrress($adrress) {
		$this->adrress = $adrress;
	}

	/**
	 * Returns the country
	 *
	 * @return \string $country
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Sets the country
	 *
	 * @param \string $country
	 * @return void
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * Returns the zipcode
	 *
	 * @return \string $zipcode
	 */
	public function getZipcode() {
		return $this->zipcode;
	}

	/**
	 * Sets the zipcode
	 *
	 * @param \string $zipcode
	 * @return void
	 */
	public function setZipcode($zipcode) {
		$this->zipcode = $zipcode;
	}

	/**
	 * Returns the zipcodeSearch
	 *
	 * @return \string $zipcodeSearch
	 */
	public function getZipcodeSearch() {
		return $this->zipcodeSearch;
	}

	/**
	 * Sets the zipcodeSearch
	 *
	 * @param \string $zipcodeSearch
	 * @return void
	 */
	public function setZipcodeSearch($zipcodeSearch) {
		$this->zipcodeSearch = $zipcodeSearch;
	}

	/**
	 * Returns the lat
	 *
	 * @return \string $lat
	 */
	public function getLat() {
		return $this->lat;
	}

	/**
	 * Sets the lat
	 *
	 * @param \string $lat
	 * @return void
	 */
	public function setLat($lat) {
		$this->lat = $lat;
	}

	/**
	 * Returns the lng
	 *
	 * @return \string $lng
	 */
	public function getLng() {
		return $this->lng;
	}

	/**
	 * Sets the lng
	 *
	 * @param \string $lng
	 * @return void
	 */
	public function setLng($lng) {
		$this->lng = $lng;
	}

	/**
	 * Returns the latLngIsSet
	 *
	 * @return boolean $latLngIsSet
	 */
	public function getLatLngIsSet() {
		return $this->latLngIsSet;
	}

	/**
	 * Sets the latLngIsSet
	 *
	 * @param boolean $latLngIsSet
	 * @return void
	 */
	public function setLatLngIsSet($latLngIsSet) {
		$this->latLngIsSet = $latLngIsSet;
	}

	/**
	 * Returns the boolean state of latLngIsSet
	 *
	 * @return boolean
	 */
	public function isLatLngIsSet() {
		return $this->getLatLngIsSet();
	}

}
?>