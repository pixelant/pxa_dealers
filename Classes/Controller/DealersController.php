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

use Pixelant\PxaDealers\Domain\Model\Dealer;
use Pixelant\PxaDealers\Domain\Model\DTO\Demand;
use Pixelant\PxaDealers\Domain\Model\DTO\Search;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DealersController extends AbstractController
{
    /**
     * Map action initialize.
     */
    public function initializeAction(): void
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addInlineLanguageLabelFile(
            'EXT:pxa_dealers/Resources/Private/Language/locallang.xlf',
            'js.'
        );

        if (!($this->settings['map']['disableGoogleapisJs'] ?? false)) {
            $pageRenderer->addJsFooterLibrary(
                'pxa_google_api',
                'https://maps.googleapis.com/maps/api/js?key=' . $this->settings['map']['googleJavascriptApiKey'],
                null,
                false,
                false,
                '',
                true
            );
        }
    }

    /**
     * action map.
     *
     * @return void
     */
    public function mapAction(): void
    {
        $this->renderMap();
    }

    /**
     * @param Search $search
     */
    public function searchAction(Search $search): void
    {
        $this->renderMap($search);
    }

    /**
     * @param Search|null $search
     * @param bool $secondarySearch Broadens the search fields
     */
    protected function renderMap(Search $search = null, bool $secondarySearch = false): void
    {
        $demand = Demand::getInstance($this->settings['demand']);

        if ($search !== null) {
            $searchFieldsPropertyName = 'searchFields';

            if ($secondarySearch) {
                if (!$search->isSearchInRadius()) {
                    $searchFieldsPropertyName = 'secondarySearchFields';
                }

                $search->setSearchInRadius(false);
            }

            $search->setSearchFields(GeneralUtility::trimExplode(
                ',',
                $this->settings['search'][$searchFieldsPropertyName],
                true
            ));
            $demand->setSearch($search);

            if ($search->isSearchInRadius() && !empty($this->settings['map']['googleServerApiKey'])) {
                if (empty($search->getLat()) || empty($search->getLng())) {
                    // Get from address
                    [$lat, $lng] = $this->getAddressInfo($search->getSearchTermOriginal());
                } else {
                    // Use user position
                    $lat = $search->getLat();
                    $lng = $search->getLng();
                }

                if ($lat && $lng) {
                    $search->setLat($lat);
                    $search->setLng($lng);
                    $search->setRadius((int) ($this->settings['search']['radius']) ?: 100);

                    $searchCenter = [
                        'lat' => $lat,
                        'lng' => $lng,
                    ];
                } else {
                    $search->setSearchInRadius(false);
                }
            }
        }

        $allCategoriesUids = [];
        $allCountriesUids = [];

        $demandedDealers = $this->dealerRepository->findDemanded($demand);

        if (!$secondarySearch && $demandedDealers->count() === 0) {
            $this->renderMap($search, true);

            return;
        }

        $dealers = [];
        $dealersOnMap = [];

        /** @var Dealer $dealer */
        foreach ($demandedDealers as $dealer) {
            if ((int)$dealer->getLat() !== 0 && (int)$dealer->getLng() !== 0) {
                $dealersOnMap[$dealer->getUid()] = $dealer;
            }
            $dealers[$dealer->getUid()] = $dealer;

            $allCategoriesUids = array_merge($allCategoriesUids, $dealer->getCategoriesAsUidsArray());
            $allCountriesUids[] = $dealer->getCountryUid();
        }

        $this->view->assignMultiple([
            'dealers' => $dealers,
            'dealersOnMap' => $dealersOnMap,
            'allCategoriesUids' => implode(',', array_unique($allCategoriesUids)),
            'allCountriesUids' => implode(',', array_unique($allCountriesUids)),
            'searchCenter' => $searchCenter ?? ['lat' => 0, 'lng' => 0],
        ]);
    }
}
