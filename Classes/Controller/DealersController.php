<?php
namespace PXA\PxaDealers\Controller;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;

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
		$args = $this->request->getArguments();
		$status = $this->checkStatus($args);

		if($status == self::searchValueOK) {
			$zipcode = preg_replace('/\s+/', '', $args['searchValue']);
			if($args['searchBy'] == 1) {
				$dealers = $this->dealersRepository->getDealersByCity($args['searchValue'],$this->settings['resultLimit']);
			} else {
				$dealers = $this->dealersRepository->getDealersByZipCode($args['searchValue'],$this->settings['resultLimit']);
			}

			$checkDealers = $this->checkDealers($dealers,$defaultCountry);

		    if($dealers->count() > 0) { 
		    	if($checkDealers){
		    		$amountOfDealers = $dealers->count();
					$jsArray = $this->generateJSOfDealers($dealers,$dealers->count());

			        $this->view->assign('jsArray', $jsArray);

			        $GLOBALS['TSFE']->additionalFooterData['googleApi'] = "<script src='https://maps.googleapis.com/maps/api/js?callback=initializeMapPxaDealers'></script>";	
			        $this->view->assign('errorApi',0);
		    	}else{
		    		$this->view->assign('errorApi',1);
		    	}
	        } else {
	        	$GLOBALS['TSFE']->additionalFooterData['googleApi'] = "<script src='https://maps.googleapis.com/maps/api/js?callback=showDefaultMap'></script>";
	        }

	        $this->view->assign('dealers',$dealers);					
		    $this->view->assign('searchValue',$args['searchValue']);
		} else {
			$GLOBALS['TSFE']->additionalFooterData['googleApi'] = "<script src='https://maps.googleapis.com/maps/api/js?callback=showDefaultMap'></script>";
		}
		$this->view->assign('status',$status);		
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
		if($this->checkApi()){
			$checkDealers = $this->checkDealers($dealers);
		}
		
		$dealers = $dealers->toArray();

		$dealersWithDistance = array();
		$finalDealersArray = array();

		foreach ($dealers as $key => $dealer) {
			if($dealer->getLatLngIsSet()) {
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
		
		$jsArray = $this->generateJSOfDealers($finalDealersArray,$amountOfDealers);

		$this->view->assignMultiple(array(
			'settings' => $this->settings['findClosestAjax'],
			'dealers' => $finalDealersArray,
			'jsArray' => $jsArray
		));

		$data = array(
			'html' => $this->view->render(),
			'count' => $amountOfDealers,
		);
		
		return json_encode($data);
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
        	
            $jsArray .= "{name: '".str_replace ("'","\'",$dealer->getName()).
            		"', lat: '".$dealer->getLat().
            		"', lng: '".$dealer->getLng().
            		"', address: '".str_replace ("'","\'",$dealer->getAdrress()).
            		"', zipcode: '".$dealer->getZipcode().
            		"', city: '".str_replace("'","\'",$dealer->getCity()).
            		"', telephone: '".$dealer->getTelephone().
            		"', telephone_clear: '".str_replace(array(' ','-'),'',$dealer->getTelephone()).
            		"', website: '".$dealer->getWebsite().
            		"', email: '".$dealer->getEmail().
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
	 * Check if Google API KEY is valid
	 *
	 * @return  boolean
	 */
	protected function checkApi() {
		// Check if Google Api valid
		if(isset($this->settings['geoLocationApiKey']) && !empty($this->settings['geoLocationApiKey'])){
			$url = "https://maps.google.com/maps/api/geocode/json";
			$url .= "?key=".$this->settings['geoLocationApiKey'];
		}else{
			$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=testAddress";
		}

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $resp_json = curl_exec($c);
        curl_close($c);        

        $resp = json_decode($resp_json, true);

        if($resp['status'] == 'REQUEST_DENIED') {
            return FALSE;
        }
        if($resp['status'] == 'OVER_QUERY_LIMIT') {
            return FALSE;
        }
        return TRUE;
	}

	/**
	 * Check if dealer was changed. And if it was get Lat and Lng
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $dealers
	 * @return boolean 
	 */
	protected function checkDealers(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $dealers) {
		$result = TRUE;
		foreach ($dealers as $dealer) {
			if($dealer->getLatLngIsSet() == FALSE) {				
                $address = $dealer->getAdrress(). ', ' . $dealer->getZipcode() . ' ' . $dealer->getCity() . ', ' . $dealer->getCountry();
                if($this->checkApi()){
                	$response = $this->getAddress($address);
	                $dealer->setLat($response['results'][0]['geometry']['location']['lat']);
	                $dealer->setLng($response['results'][0]['geometry']['location']['lng']);
	        	    $dealer->setLatLngIsSet(1);
	                $this->dealersRepository->update($dealer);
                }else{
                	$result = FALSE;
                }
			}
		}

		return $result;
	}

	/**
	 * Return lat & lng coordinates of dealer
	 *
	 * @param string $address
	 * @return array
	 */
	protected function getAddress($address) {
		if(isset($this->settings['geoLocationApiKey']) && !empty($this->settings['geoLocationApiKey'])){
			$url = "https://maps.google.com/maps/api/geocode/json?sensor=false&address=";
			$url .= urlencode($address);
			$url .= "&key=".$this->settings['geoLocationApiKey'];
		}else{
			$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=";
			$url .= urlencode($address);
		}
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
            if($resp['status'] == 'REQUEST_DENIED') {
                throw new InvalidVariableException('REQUEST_DENIED from Google API', 1388149150);
            }
            if ($resp['status'] == 'OVER_QUERY_LIMIT') {
                usleep(2000000);
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
