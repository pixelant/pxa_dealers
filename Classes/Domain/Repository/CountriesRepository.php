<?php
namespace PXA\PxaDealers\Domain\Repository;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as du;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andriy <andriy@pixelant.se>, Pixelant
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
class CountriesRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
   	*  objectManager
   	*
	* @var \TYPO3\CMS\Extbase\Object\ObjectManager
	*/
  	protected $objectManager;

 	/**
	 *  dealersRepository
	 *
	 * @var \PXA\PxaDealers\Domain\Repository\DealersRepository
	 * @inject
	 */
	protected $dealersRepository;

	/**
	 * Find country zones by iso3
	 *
	 * @param string iso3
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */

	public function getCountryZonesByIso3($iso3) {

		$localNames = array();

        // Check if static info table extension repository exists
		if ( !class_exists("\SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository") ) {
			return $localNames;
		}

		// get states
		$countryZoneRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository");

		$query = $countryZoneRepository->createQuery();
		$query->matching(
			$query->equals('countryIsoCodeA3', $iso3)
		);

        $states = $query->execute();

        if( $states->count() <= 0 ) {
        	return $localNames;
        }

		foreach ($states as $state) {
			$uid = $state->getUid();
			$localNames[$uid] = $state->getLocalName();
		}

		return $localNames;
	}

	/**
	 * Find presented countries-zones array
	 *
	 * @param string iso3
	 * @return array
	 */

	public function getAvaliableCountryZonesByCountry($country) {

		$localNames = array();

		$zonesCollection = array();

		$dealersCollection = $this->dealersRepository->findByCountry($country);

		foreach ($dealersCollection as $dealer) {
			$zone = $dealer->getCountryZone();
			if( is_object($zone) ) {
				$key = $zone->getUid();
				if( !in_array($key, $zonesCollection) ) {
					$zonesCollection[$key] = $zone->getNameEn();
				}
			}
		}

		return $zonesCollection;

	}	

}

?>