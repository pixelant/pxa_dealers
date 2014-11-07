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

use \TYPO3\CMS\Core\Utility\MathUtility;

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

			if(is_numeric($zipcode)) {
				$dealers = $this->dealersRepository->getDealersByZipCode($zipcode,$this->settings['resultLimit']);
			} else {
				$dealers = $this->dealersRepository->getDealersByCity($args['searchValue'],$this->settings['resultLimit']);
			}

			// Check is some of the dealers has no coordinates
			$this->checkDealers($dealers,$defaultCountry);

		    if($dealers->count() > 0) { 
		    	$amountOfDealers = $dealers->count();
		    	$countStep = 1;
				$jsArray = 'var markers = [';
		        foreach ($dealers as $dealer) {
		        	
		            $jsArray .= "{name: '".str_replace ("'","\'",$dealer->getName()).
		            		"', lat: '".$dealer->getLat().
		            		"', lng: '".$dealer->getLng()
	.			            "', address: '".str_replace ("'","\'",$dealer->getAdrress()).
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

		        $this->view->assign('jsArray', $jsArray);
				$GLOBALS['TSFE']->additionalJavaScript['googleApi'] = "google.maps.event.addDomListener(window, 'load', initializeMapPxaDealers);";
	        } else {
	        	$GLOBALS['TSFE']->additionalJavaScript['googleApi'] = "google.maps.event.addDomListener(window, 'load', showDefaultMap);";
	        }

	        $this->view->assign('dealers',$dealers);					
		    $this->view->assign('searchValue',$args['searchValue']);
		} else {
			
			$GLOBALS['TSFE']->additionalJavaScript['googleApi'] = "google.maps.event.addDomListener(window, 'load', showDefaultMap);";
		}

		$this->view->assign('status',$status);		
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

		foreach ($dealers as $dealer) {
			if($dealer->getLatLngIsSet() == FALSE) {				
                
                $address = $dealer->getAdrress(). ', ' . $dealer->getZipcode() . ' ' . $dealer->getCity() . ', ' . $dealer->getCountry();
                $response = $this->getAddress($address);
                
                $dealer->setLat($response['results'][0]['geometry']['location']['lat']);
                $dealer->setLng($response['results'][0]['geometry']['location']['lng']);
        	    $dealer->setLatLngIsSet(1);
                $this->dealersRepository->update($dealer);   
                              
			}
		}

		return TRUE;
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
        } while($resp['status'] == 'OVER_QUERY_LIMIT');
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