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
class DealersController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * if search did started
	 */
	const searchDidntStart = 0;

	/**
	 * if search started but search value is empty
	 */
	const searchValueEmpty = 1;

	/**
	 * if search started and search value is OK
	 */
	const searchValueOK = 2;

	/**
	 *  dealersRepository
	 *
	 * @var \PXA\PxaDealers\Domain\Repository\DealersRepository
	 * @inject
	 */
	protected $dealersRepository;

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

//		$timeStat = array();
//		$timeStat['starttime'] = microtime(true);

		$dealers = $this->dealersRepository->findAll();

//		$timeStat['2'] = microtime(true);

		$dealers = $this->checkDealers($dealers);

//		$timeStat['3'] = microtime(true);

		$jsArray = $this->generateJSOfDealers($dealers,$dealers->count());

//		$timeStat['4'] = microtime(true);
//		$timeStat['diff1'] = $timeStat['2'] - $timeStat['starttime'];
//		$timeStat['diff2'] = $timeStat['3'] - $timeStat['2'];
//		$timeStat['diff3'] = $timeStat['4'] - $timeStat['3'];
//		du::var_dump($timeStat);

		$args = $this->request->getArguments();
		$searchValue = ( isset($args['searchValue']) ) ? $args['searchValue'] : false;


		$this->view->assign('countriesList', $this->getCountriesListJSON());
		$this->view->assign('searchValue', $searchValue);
		$this->view->assign('jsArray', $jsArray);
		$this->view->assign('dealers',$dealers);
	}

	/**
	 * find closest pharmacies
	 *
	 * @param float $latitude
	 * @param float $longitude
	 * @return string
	 */
	public function findClosestAjaxAction($latitude, $longitude) {
		$dealers = $this->dealersRepository->findAll();

		$this->checkDealers($dealers);
		$dealers = $dealers->toArray();

		$dealersWithDistance = array();
		$finalDealersArray = array();

		foreach ($dealers as $key => $dealer) {
			if($dealer->getLatLngIsSet() == 1) {
				$dealersWithDistance[$key]['distance'] = $this->getDistance($latitude, $longitude, $dealer->getLat(), $dealer->getLng());
				$dealersWithDistance[$key]['dealer'] = $dealer;
			}
		}
		unset($dealers); unset($dealer);

		usort($dealersWithDistance, function($a,$b){
			return $a['distance'] > $b['distance'];
		});

		$amountOfDealers = count($dealersWithDistance);
		$limit = ($this->settings['findClosestAjax']['resultLimit'] <= $amountOfDealers ? $this->settings['findClosestAjax']['resultLimit'] : $amountOfDealers);
		for ($i=0; $i < $limit; $i++) { 
			$finalDealersArray[] = $dealersWithDistance[$i]['dealer'];
		}
		unset($dealersWithDistance);

		$uidsArray = array();

		foreach($finalDealersArray as $dealer) {
			$uidsArray[] = $dealer->getUid();
		}

		return json_encode($uidsArray);
		
//		$jsArray = $this->generateJSOfDealers($finalDealersArray,$amountOfDealers);
//
//		$this->view->assign('countriesList', $this->getCountriesListJSON());
//
//		$this->view->assignMultiple(array(
//			'settings' => $this->settings['findClosestAjax'],
//			'dealers' => $finalDealersArray,
//			'jsArray' => $jsArray
//		));
//
//		$data = array(
//			'html' => $this->view->render(),
//			'count' => $amountOfDealers,
//		);
//
//		du::var_dump($data);
		
		//return json_encode($data);
	}


	/**
	 * Generate JS
	 *
	 * @param mixed $dealers
	 * @param int $amountOfDealers
	 * @return string
	 */
	protected function generateJSOfDealers($dealers,$amountOfDealers){
		$countStep = 1;
		$jsArray = 'var markers = [';
        foreach ($dealers as $dealer) {

        	$country = $dealer->getCountry();
        	$countryName = !empty( $country ) ? $country->getShortNameEn() : '';
        	$countryZone = $dealer->getCountryZone();
        	$countryZoneUid = !empty( $countryZone ) ? $countryZone->getUid() : 0;
        	
            $jsArray .= "{name: '".str_replace ("'","\'",$dealer->getName()).
            		"', lat: '".$dealer->getLat().
            		"', lng: '".$dealer->getLng().
            		"', address: '".str_replace ("'","\'",$dealer->getAdrress()).
            		"', zipcode: '".$dealer->getZipcode().
            		"', zipcodeSearch: '".$dealer->getZipcodeSearch().
            		"', city: '".str_replace("'","\'",$dealer->getCity()).
            		"', telephone: '".$dealer->getTelephone().
            		"', telephone_clear: '".str_replace(array(' ','-'),'',$dealer->getTelephone()).
            		"', website: '".$dealer->getWebsite().
            		"', email: '".$dealer->getEmail().
            		"', uid: '".$dealer->getUid().
            		"', categories: '".$dealer->getCategoriesJSON().
            		"', country: '".$dealer->getCountryUid().
					"', countryName: '".$countryName.
            		"', countryZone: '".$countryZoneUid.
            		"', countryZoneName: '".$dealer->getCountryZoneName().
            		"', countryZoneIsoCode: '".$dealer->getCountryZoneIsoCode().
            		($amountOfDealers == $countStep ? "'}" : "'},");

            $countStep++;
        }
        $jsArray .= '];';

        return $jsArray;
	}

	/**
	 * Validate status
	 *
	 * @param array $args
	 * @return  int
	 */
	protected function checkStatus($args) {
		// Check if search started
		if(isset($args['action'])) {
			// Check if search value not empty
			if(empty($args['searchValue']))
				return self::searchValueEmpty;
			else
				return self::searchValueOK;
		}

		return self::searchDidntStart;
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
					$this->dealersRepository->update($dealer);
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

	/** 
	 * Calculate distance
	 *
	 * @param float $latitudeFrom
	 * @param float $longitudeFrom
	 * @param float $latitudeTo
	 * @param float $longitudeTo
	 * @return float distance
	 */
	protected function getDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) {
		
		$earthRadius = 6371000;

		// convert from degrees to radians
	  	$latFrom = deg2rad($latitudeFrom);
	  	$lonFrom = deg2rad($longitudeFrom);
	  	$latTo = deg2rad(floatval($latitudeTo));
	  	$lonTo = deg2rad(floatval($longitudeTo));

	  	$latDelta = $latTo - $latFrom;
	  	$lonDelta = $lonTo - $lonFrom;

	  	$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
	  	return $angle * $earthRadius;
	}

	protected function getCountriesListJSON() {

		$countriesList = $this->dealersRepository->getDealersUniqueCountriesUids();

		return json_encode($countriesList);
	}

	

	/** 
	 * import once 
	 *
	 */
	public function importAction() {

		/*if(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('importStart') == 'go') {
			
			if (($handle = fopen("typo3conf/ext/pxa_dealers/Resources/Public/cb12_05112014_final.csv", "r")) !== FALSE) {
			
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			    	$dealerModel = $this->objectManager->get('PXA\\PxaDealers\\Domain\\Model\\Dealers');


			        $dealerModel->setName(trim($data[0]));
					$dealerModel->setAdrress(trim($data[1]));
					$dealerModel->setCity(trim($data[2]));
					$dealerModel->setZipcode(trim($data[3]));
					$dealerModel->setZipcodeSearch(trim($data[3]));
					$dealerModel->setCountry('Deutchland');

					
			        $this->dealersRepository->add($dealerModel);
			        if($row == 4) break;
			        $row++;
			    }
			    fclose($handle);
			}
		    
		}*/
		exit(0);
	}

}
?>