<?php
namespace PXA\PxaDealers\Domain\Repository;

use FluidTYPO3\Flux\Utility\DebuggerUtility;

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
class DealersRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
   	*  objectManager
   	*
	* @var \TYPO3\CMS\Extbase\Object\ObjectManager
	*/
  	protected $objectManager;

 	/**
	 * Find delears unique countries uids
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */

	public function getDealersUniqueCountriesUids() {

		$query = $this->createQuery();

		/* really bad way of doing things.*/

		$statement = "SELECT DISTINCT country FROM tx_pxadealers_domain_model_dealers WHERE deleted = 0 AND hidden = 0 AND country != 0";

		$parser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Storage\\Typo3DbQueryParser');
		$params = array();

		$queryParts = $parser->parseQuery($query, $params);

		foreach ($queryParts['additionalWhereClause'] as $additionalWhereClause) {
			$statement .= " AND {$additionalWhereClause}";
		}
		
		$query->statement($statement);

		$result = $query->execute(true);

		if( function_exists("array_column") ) {
			$result = array_column($result, 'country');
		} else {
			$result = \PXA\PxaDealers\Utility\HelperFunctions::array_column($result, 'country');
		}

		return $result;
	}

 	/**
	 * Find delears unique countries
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $countries
	 */

	public function getDealersUniqueCountries() {

		// get countries
		$countryRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryRepository");

		$query = $countryRepository->createQuery();
		$query->matching(
			$query->in('uid', $this->getDealersUniqueCountriesUids())
		);

  		return $query->execute();
	}

 	/**
	 * get delears unique countries as specific format JSON
	 *
	 * @return string
	 */

	public function getDealersUniqueCountriesFormatted() {

		$tsService = $this->objectManager->get("\TYPO3\CMS\Extbase\Service\TypoScriptService");
		$ts = $tsService->convertTypoScriptArrayToPlainArray( $GLOBALS['TSFE']->tmpl->setup );
		$settings = $ts['plugin']['tx_pxadealers']['settings'];

  		$results = array();

  		foreach ($this->getDealersUniqueCountries() as $country) {
  			$uid = $country->getUid();

  			if( isset($settings['nameCountryMapping'][$uid]) ) {
				$countryName = $settings['nameCountryMapping'][$uid];
  			} else {
  				$countryName =$country->getShortNameLocal();
  			}

  			$results[$uid] = $countryName;
  		}

  		asort($results);

  		return $results;
	}

	/**
	 * Find delears by zipcode
	 *
	 * @param string $zipcode
	 * @param integer $limit limit of results
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */

	public function getDealersByZipCode($zipcode, $limit = NULL) {
		
		if($limit)
			$searchResult = $this->findDealersWithLimit($zipcode,$limit);
		else 
			$searchResult = $this->findDealers($zipcode);
		
		return $searchResult;
	}

	/**
	 * Find delears by city
	 *
	 * @param string $city
	 * @param integer $limit limit of results
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */

	public function getDealersByCity($city, $limit = NULL) {
		
		$query = $this->createQuery();

		$query->matching($query->like('city', '%' . $city . '%'));
		
		if($limit)
			$query->setLimit((integer)$limit);
		
		return $query->execute();
	}

	/**
	 * Find delears by state
	 *
	 * @param string $state
	 * @param integer $limit limit of results
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */

	public function getDealersByState($state, $limit = NULL) {

		// Set result to be empty by default
        $query = $this->createQuery();
		$query->matching( $query->equals('uid', -1) );
        $results = $query->execute();

        // Check if static info table extension repository exists
		if ( !class_exists("\SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository") ) {
			return $results;
		}

		// get states
		$countryZoneRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository");

		$query = $countryZoneRepository->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->like('localName', $state . '%'),
				$query->equals('countryIsoCodeA3', "USA")
			)
		);

        $states = $query->execute();

        // loop through states and serach for dealers
		foreach ($states as $state) {

			$query = $this->createQuery();
			$query->matching( $query->like('zipcode', $state->getIsoCode() . '%') );
			if( is_numeric($limit) ) {
				$currentLimit = $limit - $results->count();
				if( $currentLimit <= 0) {
					break;
				}
				$query->setLimit( $currentLimit );
			}

			$current_results = $query->execute();

			foreach ($current_results->toArray() as $current_result) {
				$results->offsetSet(($results->count()), $current_result);
			}

		}

		return $results;
	}

	private function findDealersWithLimit($zipcode, $limit) {
		$searchedZipcodes = array();
		$queryResults = array();

		do {
			$constraints = array();

			$query = $this->createQuery();

			foreach ($searchedZipcodes as $searchedZipcode) {
				$constraints[] = $query->logicalNot($query->like('zipcode_search', $searchedZipcode . '%'));
			}
			
			if(count($constraints) > 0) {
				$constraints[] = $query->like('zipcode_search', $zipcode . '%');
				
				$query->matching($query->logicalAnd($constraints));
			}
			else
				$query->matching($query->like('zipcode_search', $zipcode . '%'));
			
			$query->setLimit((integer)$limit);
			
			$queryResults[] = $query->execute();
			
			$lastQuery = end($queryResults);
			$limit -= $lastQuery->count();

			if(strlen($zipcode) > 1) {
				$searchedZipcodes[] = $zipcode;
				$zipcode = substr($zipcode,0,(strlen($zipcode)-1));
			} else {
				$limit = 0;
			}
			
		} while($limit > 0);


		return $this->processQueryResult($queryResults);
	}


	private function processQueryResult($queryResults) {
		$searchResult = $queryResults[0];
		unset($queryResults[0]);

		foreach ($queryResults as $queryResult) {
			foreach ($queryResult as $singleResult) {
				$offset = $searchResult->count();				
				$searchResult->offsetSet($offset,$singleResult);
			}			
		}

		return $searchResult;
	}

	private function findDealers($zipcode) {
		$searchedZipcodes = array();
		$queryResults = array();
		$originalZipcode = $zipcode;

		do {
			$constraints = array();

			$query = $this->createQuery();

			foreach ($searchedZipcodes as $searchedZipcode) {
				$constraints[] = $query->logicalNot($query->like('zipcode_search', $searchedZipcode . '%'));
			}
			
			if(count($constraints) > 0) {
				$constraints[] = $query->like('zipcode_search', $zipcode . '%');
				
				$query->matching($query->logicalAnd($constraints));
			}
			else
				$query->matching($query->like('zipcode_search', $zipcode . '%'));
			
			$queryResults[] = $query->execute();
			
			$lastQuery = end($queryResults);
			if(($originalZipcode == $zipcode) && ($lastQuery->count() >= 12))
				break;
			
			$searchedZipcodes[] = $zipcode;
			$zipcode = substr($zipcode,0,(strlen($zipcode)-1));
			
		} while(strlen($zipcode) > 0);


		return $this->processQueryResult($queryResults);
	}

	/**
	 * Find delears by name and position
	 *
	 * @param string $name
	 * @param string $lat
	 * @param string $lng
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */
	public function findByNameAndPosition($name, $lat, $lng) {

		$query = $this->createQuery();

		$query->matching(
			$query->logicalAnd(
				$query->equals("name", $name),
				$query->equals("lat", $lat),
				$query->equals("lng", $lng)
			)
		);

		return $query->execute();
	}
}
?>