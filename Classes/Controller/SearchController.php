<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Controller;

/*
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
 */

use Pixelant\PxaDealers\Domain\Model\DTO\Search;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SearchController extends AbstractController
{
    /**
     * Allowed search criterias.
     *
     * @var array
     */
    protected $searchAllowedProperties = [
        'searchTermLowercase',
        'searchTermOriginal',
        'searchInRadius',
        'pid',
    ];

    /**
     * @return void
     */
    protected function initializeSuggestAction(): void
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
     * Search form.
     * @param Search|null $search
     */
    public function formAction(Search $search = null): void
    {
        $this->view->assign(
            'storagePageIds',
            implode(',', $this->dealerRepository->getStoragePageIds())
        );

        if ($search !== null) {
            $this->view->assign('searchTermOriginal', $search->getSearchTermOriginal());
        }
    }

    /**
     * Suggest search results.
     *
     * @param Search $search
     * @return false|string
     */
    public function suggestAction(Search $search = null)
    {
        $response = ['db' => [], 'google' => []];
        if ($search !== null && !empty($search->getSearchTermLowercase())) {
            $search->setSearchFields(GeneralUtility::trimExplode(
                ',',
                $this->settings['search']['searchFields'],
                true
            ));

            $response['db'] = $this->dealerRepository->suggestResult($search);

            if ($search->isSearchInRadius() && !empty($this->settings['map']['googleServerApiKey'])) {
                $countryAlpha2Codes = $this->dealerRepository->getUniqueCountryFieldValues('cn_iso_2');

                $googleResponse = $this->getGoogleApi()->getPlaceSuggest(
                    $search->getSearchTermOriginal(),
                    $search->getLng() && $search->getLat() ? [$search->getLng(), $search->getLat()] : [],
                    $countryAlpha2Codes
                );

                if (is_array($googleResponse)
                    && $googleResponse['status'] === 'OK'
                    && count($googleResponse['predictions']) > 0
                ) {
                    foreach ($googleResponse['predictions'] as $prediction) {
                        $response['google'][] = $prediction['description'];
                    }
                } elseif ($googleResponse['status'] !== 'OK' && $googleResponse['status'] !== 'ZERO_RESULTS') {
                    $response['errors'][] = $googleResponse;

                    $this->logger->error('Call to Google Place Suggest API failed.', $googleResponse);
                }
            }
        }

        return json_encode($response);
    }
}
