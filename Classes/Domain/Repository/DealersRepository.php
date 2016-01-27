<?php
namespace PXA\PxaDealers\Domain\Repository;

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
	 * Find delears by dealers by city and zipcode
	 *
	 * @param string $search
	 * @param integer $limit limit of results
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
	 */

	public function getDealers($search, $limit = NULL) {
		
		if($limit)
			$searchResult = $this->findDealersWithLimit($search,$limit);
		else 
			$searchResult = $this->findDealers($search);
						
		$query = $this->createQuery();
		$query->matching($query->like('city', '%' . $search . '%'));
		if($limit)
			$query->setLimit((integer)$limit);
		$query->execute();

		$searchResult[] = $query->execute();

		return $this->processQueryResult($searchResult);
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

		//return $queryResults;
		return $this->processQueryResult($queryResults);
	}


	private function processQueryResult($queryResults) {
		$searchResult = $queryResults[0];
		unset($queryResults[0]);
		$tempArray = array();
		foreach ($queryResults as $queryResult) {
			foreach ($queryResult as $singleResult) {
				$offset = $searchResult->count();
				if(!in_array($singleResult->getUid(), $tempArray)){
					$searchResult->offsetSet($offset,$singleResult);
					$tempArray[] = $singleResult->getUid();
				}
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

		//return $queryResults;
		return $this->processQueryResult($queryResults);
	}
}
?>