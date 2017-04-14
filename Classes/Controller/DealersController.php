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

use Pixelant\PxaDealers\Domain\Model\Dealer;
use Pixelant\PxaDealers\Domain\Model\Demand;
use Pixelant\PxaDealers\Domain\Model\Search;
use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DealersController extends ActionController
{
    /**
     *  dealer repository
     *
     * @var \Pixelant\PxaDealers\Domain\Repository\DealerRepository
     * @inject
     */
    protected $dealerRepository;

    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     * @inject
     */
    protected $pageRenderer;

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
     * Initialize map
     *
     * @return void
     */
    public function initializeMapAction()
    {
        $this->getFrontendLabels();
        $this->loadGoogleApi();
    }

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
     * action map
     *
     * @param \Pixelant\PxaDealers\Domain\Model\Search $search
     * @return void
     */
    public function mapAction(Search $search = null)
    {
        $dealers = [];

        $demand = Demand::getInstance($this->settings['demand']);
        $demandDealers = $this->dealerRepository->findDemanded($demand);

        if ($search !== null) {
            $search->setSearchFields(GeneralUtility::trimExplode(
                ',',
                $this->settings['search']['searchFields'],
                true
            ));
            $demand->setSeach($search);
        }

        $allCategoriesUids = [];
        $allCountriesUids = [];

        /** @var Dealer $dealer */
        foreach ($demandDealers as $dealer) {
            $dealers[$dealer->getUid()] = $dealer->toArray();
            $allCategoriesUids = array_merge($allCategoriesUids, $dealer->getCategoriesAsUidsArray());

            if (!in_array($dealer->getCountryUid(), $allCountriesUids, true)) {
                $allCountriesUids[] = $dealer->getCountryUid();
            }
        }

        $this->view->assignMultiple([
            'dealers' => $dealers,
            'allCategoriesUids' => implode(',', array_unique($allCategoriesUids)),
            'allCountriesUids' => implode(',', $allCountriesUids),
        ]);
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

        $this->response->setHeader('Content-Type', 'application/json');
        $this->view->assign('data', isset($response) ? $response : []);
    }

    /**
     * Incldue google api only on map page
     */
    protected function loadGoogleApi()
    {
        $pathGoogleMaps = sprintf(
            'https://maps.googleapis.com/maps/api/js?key=%s',
            $this->settings['map']['googleJavascriptApiKey']
        );

        $this->pageRenderer->addJsFooterLibrary('googleapis', $pathGoogleMaps);
    }

    /**
     * Add labels for JS
     *
     * @return void
     */
    protected function getFrontendLabels()
    {
        /** @var LocalizationFactory $languageFactory */
        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);

        $langKey = MainUtility::getTSFE()->config['config']['language'];
        $labels = $languageFactory->getParsedData(
            'EXT:pxa_dealers/Resources/Private/Language/locallang.xlf',
            $langKey ? $langKey : 'en'
        );

        if (!empty($labels[$langKey])) {
            $labels = $labels[$langKey];
        } else {
            $labels = $labels['default'];
        }

        $labelsJs = [];
        foreach (array_keys($labels) as $key) {
            if (GeneralUtility::isFirstPartOfStr($key, 'js.')) {
                $labelsJs[$key] = LocalizationUtility::translate($key, $this->extensionName);
            }
        }

        $this->pageRenderer->addInlineLanguageLabelArray($labelsJs);
    }
}
