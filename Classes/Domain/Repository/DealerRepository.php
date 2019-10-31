<?php

namespace Pixelant\PxaDealers\Domain\Repository;

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

use Pixelant\PxaDealers\Domain\Model\Dealer;
use Pixelant\PxaDealers\Domain\Model\DTO\Demand;
use Pixelant\PxaDealers\Domain\Model\DTO\Search;
use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DealerRepository extends AbstractDemandRepository
{

    /**
     * @param Search $search
     * @return array
     */
    public function suggestResult(Search $search)
    {
        $query = $this->createQuery();
        $sword = $search->getSearchTermLowercase();

        if ($search->getPid()) {
            $query->getQuerySettings()->setStoragePageIds(
                GeneralUtility::intExplode(',', $search->getPid(), true)
            );
        }

        $result = [];

        foreach ($search->getSearchFields() as $searchField) {
            $this->suggestByField($query, $sword, $result, $searchField);
        }

        return array_unique($result, SORT_STRING);
    }

    /**
     * Check for storage
     *
     * @return array
     */
    public function getStoragePageIds()
    {
        $query = $this->createQuery();
        return $query->getQuerySettings()->getStoragePageIds();
    }

    /**
     * @param QueryInterface $query
     * @param Demand $demand
     * @return void
     */
    protected function createConstraints(QueryInterface $query, Demand $demand)
    {
        // If search by radius just create a query
        if ($demand->getSearch() !== null && $demand->getSearch()->isSearchInRadius()) {
            $storage = $query->getQuerySettings()->getStoragePageIds();

            $statement = sprintf(
                'SELECT *, ( 6371 * acos( cos( radians(\'%s\') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(\'%s\') ) + sin( radians(\'%s\') ) * sin( radians( lat ) ) ) ) AS distance FROM tx_pxadealers_domain_model_dealer %s %s HAVING distance < \'%s\' ORDER BY distance',
                (float)$demand->getSearch()->getLat(),
                (float)$demand->getSearch()->getLng(),
                (float)$demand->getSearch()->getLat(),
                'WHERE ' . (empty($storage) ? '1=1' : ('pid IN(' . implode(',', $storage) . ')')),
                MainUtility::getTSFE()->cObj->enableFields('tx_pxadealers_domain_model_dealer'),
                (int)$demand->getSearch()->getRadius()
            );

            $query->statement($statement);
        } else {
            $constraintsAnd = [];
            $constraintsOr = [];
            $constraints = [];

            // set country restriction
            if (!empty($demand->getCountries())) {
                $constraintsAnd[] = $query->in('country', $demand->getCountries());
            }

            // set categories restriction
            if (!empty($demand->getCategories())) {
                $constraintsAnd[] = $query->contains('categories', $demand->getCategories());
            }

            if ($demand->getSearch() !== null) {
                foreach ($demand->getSearch()->getSearchFields() as $searchField) {
                    $constraintsOr[] = $query->like(
                        $searchField,
                        '%' . $demand->getSearch()->getSearchTermLowercase() . '%'
                    );
                }
            }

            if (!empty($constraintsAnd)) {
                $constraints[] = $query->logicalAnd($constraintsAnd);
            }

            if (!empty($constraintsOr)) {
                $constraints[] = $query->logicalOr($constraintsOr);
            }

            if (count($constraints) > 1) {
                $query->matching(
                    $query->logicalAnd($constraints)
                );
            } elseif (count($constraints) === 1) {
                $query->matching($constraints[0]);
            }
        }
    }

    /**
     * Set orderings
     *
     * @param QueryInterface $query
     * @param Demand $demand
     */
    protected function setOrdering(QueryInterface $query, Demand $demand)
    {
        // Set orderings only in case of default search
        if ($demand->getSearch() === null || !$demand->getSearch()->isSearchInRadius()) {
            parent::setOrdering($query, $demand);
        }
    }

    /**
     * Append suggest result
     *
     * @param QueryInterface $query
     * @param $sword
     * @param $result
     * @param $field
     */
    private function suggestByField(QueryInterface $query, $sword, &$result, $field)
    {
        $dealers = $query
            ->matching(
                $query->like($field, '%' . $sword . '%')
            )
            ->execute();

        if ($dealers->count() > 0) {
            /** @var Dealer $dealer */
            foreach ($dealers as $dealer) {
                $propertyParts = GeneralUtility::trimExplode('.', $field);
                if (count($propertyParts) === 1) {
                    $result[]= ObjectAccess::getProperty($dealer, $field);
                } else {
                    $childObject = ObjectAccess::getProperty($dealer, $propertyParts[0]);
                    $result[] = ObjectAccess::getProperty($childObject, $propertyParts[1]);
                }
            }
        }
    }
}
