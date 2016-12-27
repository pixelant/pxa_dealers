<?php


namespace Pixelant\PxaDealers\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
class FilterController extends ActionController
{
    /**
     *  categoriesRepository
     *
     * @var \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoriesRepository;

    /**
     *  categoriesFilterOptionRepository
     *
     * @var \Pixelant\PxaDealers\Domain\Repository\CategoriesFilterOptionRepository
     * @inject
     */
    protected $categoriesFilterOptionRepository;

    /**
     * countryRepository
     *
     * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
     * @inject
     */
    protected $countryRepository;

    /**
     * Filter by categories collections
     *
     * @return void
     */
    public function categoriesCollectionFilterAction()
    {
        $this->view->assignMultiple([
            'categoriesCollections' => $this->categoriesFilterOptionRepository->findByUids(GeneralUtility::intExplode(',', $this->settings['filter']['categoriesFilterOptions'])),
            'uid' => $this->configurationManager->getContentObject()->data['uid']
        ]);
    }

    /**
     * Categories filter plugin
     *
     * @return void
     */
    public function categoriesFilterAction()
    {
        $this->view->assignMultiple([
            'categories' => $this->getCategories(),
            'uid' => $this->configurationManager->getContentObject()->data['uid']
        ]);
    }

    /**
     * Countries filter
     *
     * @return void
     */
    public function countriesFilterAction()
    {
        $this->view->assignMultiple([
            'countries' => $this->getCountries(),
            'uid' => $this->configurationManager->getContentObject()->data['uid']
        ]);
    }

    /**
     * Get list of cuntires
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getCountries()
    {
        if (!empty($this->settings['demand']['countries'])) {
            $countriesUid = GeneralUtility::intExplode(',', $this->settings['demand']['countries']);

            $query = $this->countryRepository->createQuery();

            $query->matching(
                $query->in('uid', $countriesUid)
            );

            return $query->execute();
        } else {
            return $this->countryRepository->findAll();
        }
    }

    /**
     * Get categories
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getCategories()
    {
        if (!empty($this->settings['demand']['categories'])) {
            $categoriesUids = GeneralUtility::intExplode(',', $this->settings['demand']['categories']);

            $query = $this->categoriesRepository->createQuery();

            if (count($categoriesUids) > 1) {
                $criterion = $query->in('uid', $categoriesUids);
            } else {
                $criterion = $query->equals('parent', $categoriesUids[0]);
            }

            $query->matching(
                $criterion
            );

            return $query->execute();
        } else {
            return $this->categoriesRepository->findAll();
        }
    }
}