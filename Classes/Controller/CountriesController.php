<?php

namespace Pixelant\PxaDealers\Controller;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CountriesController extends ActionController
{
    /**
     * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
     * @inject
     */
    protected $countriesRepository;

    /**
     * Countries filter
     *
     * @return void
     */
    public function countriesFilterAction()
    {
        $this->view->assign('countries', $this->getCountries());
    }

    /**
     * Get list of cuntires
     *
     * @return array
     */
    protected function getCountries()
    {
        if (!empty($this->settings['demand']['countries'])) {
            $countriesUids = GeneralUtility::intExplode(',', $this->settings['demand']['countries']);

            $query = $this->countriesRepository->createQuery();

            $query->matching(
                $query->in('uid', $countriesUids)
            );

            $result = $query->execute();
        } else {
            $result = $this->countriesRepository->findAll();
        }

        return $this->countriesRepository->localizedSort(
            $result,
            'asc'
        );
    }
}
