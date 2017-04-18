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

use Pixelant\PxaDealers\Domain\Model\Search;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SearchController extends ActionController
{
    /**
     *  dealer repository
     *
     * @var \Pixelant\PxaDealers\Domain\Repository\DealerRepository
     * @inject
     */
    protected $dealerRepository;

    /**
     * Allowed search criterias
     *
     * @var array
     */
    protected $searchAllowedProperties = [
        'searchTermLowercase',
        'searchTermOriginal',
        'pid'
    ];

    /**
     * @return void
     */
    protected function initializeSuggestAction()
    {
        /** @var PropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this->arguments['search']->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowProperties(...$this->searchAllowedProperties);
        $propertyMappingConfiguration->setTypeConverterOption(
            PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
            true
        );
    }

    /**
     * Search form
     */
    public function searchAction()
    {
        $this->view->assign(
            'storagePageIds',
            implode(',', $this->dealerRepository->getStoragePageIds())
        );

        if ($this->request->hasArgument('search')) {
            $search = $this->request->getArgument('search');
            $this->view->assign('searchTermOriginal', $search['searchTermOriginal']);
        }
    }

    /**
     * Suggest search results
     *
     * @param \Pixelant\PxaDealers\Domain\Model\Search $search
     */
    public function suggestAction(Search $search = null)
    {
        if ($search !== null && !empty($search->getSearchTermLowercase())) {
            $search->setSearchFields(GeneralUtility::trimExplode(
                ',',
                $this->settings['search']['searchFields'],
                true
            ));

            $response = $this->dealerRepository->suggestResult($search);
        }

        $this->view->assign('data', isset($response) ? $response : []);
    }
}
