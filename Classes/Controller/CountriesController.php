<?php
namespace PXA\PxaDealers\Controller;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility as lu;

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
class CountriesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 *  countriesRepository
	 *
	 * @var \PXA\PxaDealers\Domain\Repository\CountriesRepository
	 * @inject
	 */
	protected $countriesRepository;

	/**
	 *  dealersRepository
	 *
	 * @var \PXA\PxaDealers\Domain\Repository\DealersRepository
	 * @inject
	 */
	protected $dealersRepository;

	/**
   	*  objectManager
   	*
	* @var \TYPO3\CMS\Extbase\Object\ObjectManager
	*/
  	protected $objectManager;

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {

		// Get extension name
		$extensionName =  \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase(
			$this->controllerContext->getRequest()->getControllerExtensionKey()
		);

		$language = $GLOBALS['TSFE']->sys_language_uid;

		$selected = 0;

		if( isset($this->settings['languageCountryMapping'][$language]) ) {
			$selected = $this->settings['languageCountryMapping'][$language];
		}

		$mainCountries = explode(',', $this->settings['mainCountries']);

		// Trim and deleted empty elements from mainCountries array
		$mainCountries = array_map('trim', $mainCountries);
		$mainCountries = array_filter($mainCountries, function($val) {
			if( !empty($val) ) {
				return true;
			} else {
				return false;
			}
		});

		$countries = $this->dealersRepository->getDealersUniqueCountriesFormatted();

		$diffCountries = array();

		if(!empty($mainCountries)){
			
			$diffCountries = array_flip(array_diff(array_flip($countries), $mainCountries));

			if(!empty($diffCountries)) {
				$countries = array_diff_key($countries, $diffCountries);
			}
		}

		$countryNames = $this->countriesRepository->getCountryNamesForUids( array_keys($countries) );
		$this->view->assign('countryNames', json_encode( $countryNames ));

		$countryZonesCollection = array();

		$countryRepository = $this->objectManager->get("SJBR\StaticInfoTables\Domain\Repository\CountryRepository");

		// get all country-zones for all presented countries
		if( $this->settings['dealers_countries_states_selector'] ) {

			// Get country zones
			foreach ($countries as $countryKey => $countryName) {
					$country = $countryRepository->findByUid($countryKey);
					$countryZonesCollection[$countryKey] = $this->countriesRepository->getAvaliableCountryZonesByCountry( $country );
			}
		}

		if( !empty($diffCountries) ) {
			$countries = $countries + array(
				'row' => lu::translate("country_list.other_countries", $extensionName)
			);	
		}

		if($this->settings['showAllCountriesOption'] === "1" && count($countries) > 1) {
			$countries = array(
					0 => lu::translate("country_list.all_countries", $extensionName)
			) + $countries;
		}

		$this->view->assign('countries', $countries);
		$this->view->assign('countryZones', json_encode( $countryZonesCollection ));
		$this->view->assign('selectedLanguage', $selected);
	}

	public function countriesSelectorAction(){
	}

	public function statesSelectorAction(){

		$countryRepository = $this->objectManager->get("SJBR\StaticInfoTables\Domain\Repository\CountryRepository");

		// Get country
		$countryObject = $countryRepository->findByUid($this->settings['statesCountry']);

		$countries = [];
		$countries[$this->settings['statesCountry']] = $countryObject->getShortNameEn();

		// Get country zones
		$countryZonesCollection = array();
		$countryZonesCollection[$this->settings['statesCountry']] = $this->countriesRepository
			->getAvaliableCountryZonesByCountry( $countryObject );

		$this->view->assign('countries', $countries);
		$this->view->assign('countryZones', json_encode( $countryZonesCollection ));
	}

	public function zipCitySearchAction(){
	}

}
?>