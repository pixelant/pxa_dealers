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
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SearchController extends AbstractConroller
{
    /**
     * Google api to suggest places
     */
    const PLACE_SUGGEST_URL = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=%s&types=geocode&language=%s&key=%s';

    /**
     * Allowed search criterias
     *
     * @var array
     */
    protected $searchAllowedProperties = [
        'searchTermLowercase',
        'searchTermOriginal',
        'searchInRadius',
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
    }

    /**
     * This is same as map just no-cache for search results
     *
     * @param \Pixelant\PxaDealers\Domain\Model\Search $search
     * @return void
     */
    public function searchResultsAction(Search $search = null)
    {
        $this->renderMap($search);

        if ($search !== null) {
            $this->view->assign('searchTermOriginal', $search->getSearchTermOriginal());
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

            if ($search->isSearchInRadius() && !empty($this->settings['map']['googleServerApiKey'])) {
                $apiUrl = sprintf(
                    self::PLACE_SUGGEST_URL,
                    $search->getSearchTermOriginal(),
                    $GLOBALS['TSFE']->config['language'] ?: 'en',
                    $this->settings['map']['googleServerApiKey']
                );
                $googleResponse = json_decode(GeneralUtility::getUrl($apiUrl), true);

                if (is_array($googleResponse)
                    && $googleResponse['status'] === 'OK'
                    && count($googleResponse['predictions']) > 0
                ) {
                    $response = [];
                    foreach ($googleResponse['predictions'] as $prediction) {
                        $response[] = $prediction['description'];
                    }
                }
            } else {
                $response = $this->dealerRepository->suggestResult($search);
            }
        }

        $this->view->assign('data', isset($response) ? $response : []);
    }
}
