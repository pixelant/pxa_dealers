<?php
declare(strict_types=1);
/*
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
 */

namespace Pixelant\PxaDealers\Domain\Repository;

use Pixelant\PxaDealers\Domain\Model\DTO\Demand;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class CategoryRepository.
 */
class CategoryRepository extends AbstractDemandRepository
{
    /**
     * @param QueryInterface $query
     * @param Demand $demand
     */
    protected function createConstraints(QueryInterface $query, Demand $demand): void
    {
        $categories = $demand->getCategories();

        if (!empty($categories)) {
            if (count($categories) > 1) {
                $criterion = $query->in('uid', $categories);
            } else {
                $criterion = $query->equals('parent', $categories[0]);
            }

            $query->matching(
                $criterion
            );
        }
    }

    /**
     * @param QueryInterface $query
     * @param Demand $demand
     */
    protected function setOrdering(QueryInterface $query, Demand $demand): void
    {
        if ($demand->getOrderBy() === 'name') {
            $demand->setOrderBy('title');
        }

        parent::setOrdering($query, $demand);
    }
}
