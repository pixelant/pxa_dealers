<?php
namespace PXA\PxaDealers\Controller;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as du;

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

		//$tsCountries = $this->settings['countries'];

		//$selected = key($tsCountries[$GLOBALS['TSFE']->sys_language_uid]);

		$language = $GLOBALS['TSFE']->sys_language_uid;

		// du::var_dump($language);
		// du::var_dump($this->settings['languageCountryMapping']);

		$selected = 0;

		if( isset($this->settings['languageCountryMapping'][$language]) ) {
			$selected = $this->settings['languageCountryMapping'][$language];
		}

		//du::var_dump($this->settings);

		$mainCountries = explode(',', $this->settings['mainCountries']);

		$countries = $this->dealersRepository->getDealersUniqueCountriesFormatted();

		$mainCountriesCollection = array_flip(array_intersect(array_flip($countries), $mainCountries));

		if(!empty($mainCountriesCollection) && count($mainCountriesCollection) != count($countries) ) {
			$countries = $mainCountriesCollection;
		}

		$countryZonesCollection = array();

		$countryRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryRepository");

		// get all country-zones for all presented countries
		if( $this->settings['dealers_countries_states_selector'] ) {

			// Get country zones
			foreach ($countries as $countryKey => $countryName) {
					$country = $countryRepository->findByUid($countryKey);
					$countryZonesCollection[$countryKey] = $this->countriesRepository->getAvaliableCountryZonesByCountry( $country );
			}
		}

		$countries = $countries + array('row' => "other countries");
		$countries = array(0 => "all") + $countries; 

		$this->view->assign('countries', $countries);
		$this->view->assign('countryZones', json_encode( $countryZonesCollection ));
		$this->view->assign('selectedLanguage', $selected);
		//$this->view->assign('mainCountries', $mainCountries);
	}

}
?>