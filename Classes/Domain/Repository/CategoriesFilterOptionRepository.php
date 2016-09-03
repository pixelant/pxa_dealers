<?php
namespace Pixelant\PxaDealers\Domain\Repository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
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
 * The repository for CategoriesFilterOptions
 */
class CategoriesFilterOptionRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @var array
     */
//    protected $defaultOrderings = array(
//        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
//    );

    public function findByUids($uids)
    {
        if( empty($uids) ) {
            return [];
        }

        if( !is_array($uids) ) {
            $uids = explode(',', $uids);
        }

        $query = $this->createQuery();
        $query->matching(
            $query->in('uid', $uids)
        );

        $results = $query->execute();

        // Sort by uid list order
        $indexedResults = array_fill_keys($uids, "1");
        foreach ($results as $resultItem) {
            $uid = $resultItem->getUid();
            $indexedResults[$uid] = $resultItem;
        }

        return $indexedResults;

    }

}