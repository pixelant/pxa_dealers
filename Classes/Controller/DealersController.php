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
use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
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
     * Initialize map
     *
     * @return void
     */
    public function initializeMapAction()
    {
        $this->includeFeCssAndJs('map');
        $this->getFrontendLabels();
    }

    /**
     * Initialize Search
     *
     * @return void
     */
    public function initializeSearchAction()
    {
        $this->includeFeCssAndJs('suggest');
    }

    /**
     * Search form
     *
     * @return void
     */
    public function searchAction()
    {
        $this->view->assignMultiple([
            'language' => MainUtility::getTSFE()->sys_language_uid
        ]);
    }

    /**
     * action map
     *
     * @return void
     */
    public function mapAction()
    {
        $dealers = [];

        $demand = Demand::getInstance($this->settings['demand']);

        /** @var Dealer $dealer */
        foreach ($this->dealerRepository->findDemanded($demand) as $dealer) {
            $dealers[$dealer->getUid()] = $dealer->toArray();
        }

        $this->view->assignMultiple([
            'dealers' => $dealers,
            'uid' => $this->configurationManager->getContentObject()->data['uid']
        ]);
    }

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

    /**
     * Include required JS and Css from TS setup
     *
     * @param string $scripts
     * @return void
     */
    protected function includeFeCssAndJs($scripts)
    {
        if (!empty($this->settings['map']['googleJavascriptApiKey'])) {
            if ($scripts === 'map') {
                // google apis
                $pathGoogleMaps = sprintf(
                    'https://maps.googleapis.com/maps/api/js?key=%s',
                    $this->settings['map']['googleJavascriptApiKey']
                );

                $this->pageRenderer->addJsFooterLibrary('googleapis', $pathGoogleMaps);
            }

            // include JS
            foreach ($this->settings['scripts'][$scripts] as $script) {
                $scriptPath = GeneralUtility::getFileAbsFileName($script);
                if (file_exists($scriptPath)) {
                    $this->pageRenderer->addJsFooterFile(PathUtility::stripPathSitePrefix($scriptPath));
                }
            }

            //include CSS
            foreach ($this->settings['styling'] as $css) {
                $cssPath = GeneralUtility::getFileAbsFileName($css);
                if (file_exists($cssPath)) {
                    $this->pageRenderer->addCssFile(PathUtility::stripPathSitePrefix($cssPath));
                }
            }
        }
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
        $labels = $languageFactory->getParsedData('EXT:pxa_dealers/Resources/Private/Language/locallang.xlf', $langKey ? $langKey : 'en');

        if (!empty($labels[$langKey])) {
            $labels = $labels[$langKey];
        } else {
            $labels = $labels['default'];
        }

        $labelsJs = [];
        foreach (array_keys($labels) as $key) {
            if (strpos($key, 'js.') === 0) {
                $labelsJs[$key] = LocalizationUtility::translate($key, $this->extensionName);
            }
        }

        $this->pageRenderer->addInlineLanguageLabelArray($labelsJs);
    }
}