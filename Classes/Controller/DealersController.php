<?php
namespace PXA\PxaDealers\Controller;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
class DealersController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 *  dealersRepository
	 *
	 * @var \PXA\PxaDealers\Domain\Repository\DealersRepository
	 * @inject
	 */
	protected $dealersRepository;

	/**
	 * init action
	 *
	 * @return void
	 */
	public function initializeAction() {
		$path_to_js = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('pxa_dealers') . "Resources/Public/Js/";

		// Include google maps lib
		if( $this->settings['includeGoogleMaps'] == 1) {
			$GLOBALS['TSFE']->getPageRenderer()->addJsFooterLibrary("googlemapsapi", "https://maps.googleapis.com/maps/api/js?sensor=false",
					'text/javascript', false, false, '', true, '|');
		}

		// Include built-in isotope lib
		if( $this->settings['includeIsotope'] == 1) {
			$GLOBALS['TSFE']->getPageRenderer()->addJsFooterFile($path_to_js . "isotope.pkgd.min.js");
		}

		// Include local scripts
		if( $this->settings['includeLocalScripts'] == 1) {
			$GLOBALS['TSFE']->getPageRenderer()->addJsFooterFile($path_to_js . "markerclusterer.js");
			$GLOBALS['TSFE']->getPageRenderer()->addJsFooterFile($path_to_js . "pxa_dealers.js");
		}
	}

	/**
	 * action searchForm
	 *
	 * @return void
	 */
	public function searchFormAction() {
		
	}

	/**
	 * action searchResults
	 *
	 * @return void
	 */
	public function searchResultsAction() {

		$args = $this->request->getArguments();

		$dealers = $this->checkDealers( $this->dealersRepository->findAll() );

		$searchValue = ( isset($args['searchValue']) ) ? $args['searchValue'] : false;

		$this->view->assign('countriesList', $this->getCountriesListJSON());
		$this->view->assign('searchValue', $searchValue);
		$this->view->assign('dealersJson', $this->generateJSOfDealers($dealers));
		$this->view->assign('dealers',$dealers);
	}

	/**
	 * Generate JS
	 *
	 * @param mixed $dealers
	 * @param int $amountOfDealers
	 * @return string
	 */
	protected function generateJSOfDealers($dealers){

		$results = array();

		foreach($dealers as $dealer) {

			$country = $dealer->getCountry();
			$countryName = !empty( $country ) ? $country->getShortNameEn() : '';
			$countryZone = $dealer->getCountryZone();
			$countryZoneUid = !empty( $countryZone ) ? $countryZone->getUid() : 0;

			$results[] = array(
				"name" => str_replace("'", "\'", $dealer->getName()),
				"lat" => $dealer->getLat(),
				"lng" => $dealer->getLng(),
				"address" => str_replace ("'","\'",$dealer->getAdrress()),
				"zipcode" => $dealer->getZipcode(),
				"zipcodeSearch" => $dealer->getZipcodeSearch(),
				"city" => str_replace("'","\'",$dealer->getCity()),
				"telephone" => $dealer->getTelephone(),
				"telephone_clear" => str_replace(array(' ','-'),'',$dealer->getTelephone()),
				"website" => $dealer->getWebsite(),
				"email" => $dealer->getEmail(),
				"uid" => (string)$dealer->getUid(),
				"categories" => $dealer->getCategoriesJSON(),
				"country" => (string)$dealer->getCountryUid(),
				"countryName" => $countryName,
				"countryZone" => (string)$countryZoneUid,
				"countryZoneName" => $dealer->getCountryZoneName(),
				"countryZoneIsoCode" => $dealer->getCountryZoneIsoCode(),
				"showStreetView" => $dealer->getShowStreetView()
			);
		}

		return json_encode( $results );

	}

	/**
	 * Check if dealer was changed. And if it was get Lat and Lng
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $dealers
	 * @return boolean 
	 */
	protected function checkDealers(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $dealers) {

		$toUnset = array();

		foreach ($dealers as $dealerKey => $dealer) {

			if($dealer->getLatLngIsSet() == 0 ) {
                
                $address = $dealer->getAdrress(). ', ' . $dealer->getZipcode() . ' ' . $dealer->getCity();

                $country = $dealer->getCountry();

                if( is_object($country) ) {
					$address .= ', ' . $country->getShortNameEn();
				}

				$cachedCoordinates = $this->getCachedCoordinates($address);
				if( $cachedCoordinates ) {
					$dealer->setLat($cachedCoordinates['lat']);
					$dealer->setLng($cachedCoordinates['lng']);
					$dealer->setLatLngIsSet(1);
					$this->dealersRepository->update($dealer);
				} else {

					$response = $this->getAddress($address);

					// Check if we actually get the location. If not set setLatLngIsSet to -1 to exclude it from returned dealers collection
					if(!$response) {
						$dealer->setLatLngIsSet(-1);
						$this->dealersRepository->update($dealer);
						$toUnset[] = $dealerKey;
					} else {
						$dealer->setLat($response['results'][0]['geometry']['location']['lat']);
						$dealer->setLng($response['results'][0]['geometry']['location']['lng']);
						$dealer->setLatLngIsSet(1);
						$this->setCachedCoordinates(
							$address,
							$response['results'][0]['geometry']['location']['lat'],
							$response['results'][0]['geometry']['location']['lng']
						);
						$this->dealersRepository->update($dealer);
					}
				}
			}

			if( $dealer->getLatLngIsSet() == -1 ) {
				$toUnset[] = $dealerKey;
			}
		}

		// Unset dealers that don't have lat or lng
		foreach ($toUnset as $keyToUnset) {
			unset($dealers[$keyToUnset]);
		}

		return $dealers;
	}

	/**
	 * Return lat & lng coordinates of dealer
	 *
	 * @param string $address
	 * @return array
	 */
	protected function getAddress($address) {
		$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=";
		$url .= urlencode($address);

		do {
            $c = curl_init();
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            $resp_json = curl_exec($c);
            curl_close($c);        

            $resp = json_decode($resp_json, true);

            if($resp['status'] == 'OK') {
                return $resp;
            }
            if ($resp['status'] == 'OVER_QUERY_LIMIT') {
                usleep(2000000);
            }
            if($resp['status'] == 'ZERO_RESULTS') {
                return false;
            }
        } while($resp['status'] == 'OVER_QUERY_LIMIT');
	}

	protected function getCountriesListJSON() {

		$countriesList = $this->dealersRepository->getDealersUniqueCountriesUids();

		return json_encode($countriesList);
	}

	public function showMapAction() {
	}

	protected function getCachedCoordinates($address) {
		
		$hash = hash("md5", strtolower($address));

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			"uid, lat, lng",
			"tx_pxadealers_coordinates_cache",
			"hash='" . $hash . "'"
		);

		return $result;

	}

	protected function setCachedCoordinates($address, $lat, $lng) {

		$addressHash = hash("md5", strtolower($address));

		$address = $GLOBALS['TYPO3_DB']->fullQuoteStr(strip_tags($address), 'tx_pxadealers_coordinates_cache');
		$lat = $GLOBALS['TYPO3_DB']->fullQuoteStr(strip_tags($lat), 'tx_pxadealers_coordinates_cache');
		$lng = $GLOBALS['TYPO3_DB']->fullQuoteStr(strip_tags($lng), 'tx_pxadealers_coordinates_cache');

		$result = $GLOBALS['TYPO3_DB'] -> sql_query(
			"INSERT IGNORE INTO tx_pxadealers_coordinates_cache (hash,address,lat,lng) VALUES ('{$addressHash}',{$address},{$lat},{$lng})"
		);

		return $result;

	}

}
?>