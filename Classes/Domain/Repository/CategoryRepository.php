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

use Doctrine\DBAL\Connection;
use Pixelant\PxaDealers\Domain\Model\DTO\Demand;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    /**
     * Get the translated Categories
     *
     * @param array $idList
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function overlayTranslatedCategoryIds(array $idList): array
    {
        $language = $this->getSysLanguageUid();
        if ($language > 0 && !empty($idList)) {
            if (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('sys_category');
                $rows = $queryBuilder
                    ->select('l10n_parent', 'uid', 'sys_language_uid')
                    ->from('sys_category')
                    ->where(
                        $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($language, \PDO::PARAM_INT)),
                        $queryBuilder->expr()->in('l10n_parent', $queryBuilder->createNamedParameter($idList, Connection::PARAM_INT_ARRAY))
                    )
                    ->execute()->fetchAll();

                $idList = $this->replaceCategoryIds($idList, $rows);
            }
        }

        return $idList;
    }

    /**
     * Get the used sys language
     *
     * @return int
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function getSysLanguageUid(): int
    {
        $sysLanguage = 0;

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() === 10) {
            $sysLanguage = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
        } elseif (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
            $sysLanguage = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'contentId');
        } elseif ((int)GeneralUtility::_GP('L')) {
            $sysLanguage = (int)GeneralUtility::_GP('L');
        }

        return $sysLanguage;
    }

    /**
     * Replace ids in array by the given ones
     *
     * @param array $idList
     * @param array $rows
     * @return array
     */
    protected function replaceCategoryIds(array $idList, array $rows): array
    {
        foreach ($rows as $row) {
            $pos = array_search($row['l10n_parent'], $idList);
            if ($pos !== false) {
                $idList[$pos] = (int)$row['uid'];
            }
        }

        return $idList;
    }
}
