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

use Pixelant\PxaDealers\Domain\Model\Dealers;
use Pixelant\PxaDealers\Domain\Model\Demand;
use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
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
class DealersController extends ActionController {

    /**
     * fields from flexform conver to array
     */
    CONST FIELDS_ARRAY = 'countries,categories';

    /**
     *  dealersRepository
     *
     * @var \Pixelant\PxaDealers\Domain\Repository\DealersRepository
     * @inject
     */
    protected $dealersRepository;

    /**
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     * @inject
     */
    protected $pageRenderer;

    /**
     * Initialuze
     *
     * @return void
     */
    public function initializeAction() {
        $this->includeFeCssAndJs();
        $this->getFrontendLabels();
    }

    /**
     * action map
     *
     * @return void
     */
    public function mapAction() {
        $dealers = [];
        $demand = Demand::getInstance(
            $this->getDemanSettings($this->settings['demand'])
        );

        /** @var Dealers $dealer */
        foreach ($this->dealersRepository->findDemanded($demand) as $dealer) {
            $dealers[] = $dealer->toArray();
        }

        $this->view->assignMultiple([
            'dealers' => $dealers
        ]);
    }

    /**
     * @param $settings
     * @return array
     */
    protected function getDemanSettings($settings) {
        foreach ($settings as $field => $value) {
            if (GeneralUtility::inList(self::FIELDS_ARRAY, $field)) {
                $settings[$field] = GeneralUtility::intExplode(',', $value, TRUE);
            }
        }

        return $settings;
    }

    /**
     * Inlcude required JS and Css from TS setup
     *
     * @return void
     */
    protected function includeFeCssAndJs() {
        if (!empty($this->settings['googleJavascriptApiKey'])) {
            // google apis
            $pathGoogleMaps = sprintf(
                'https://maps.googleapis.com/maps/api/js?language=%s&key=%s',
                $this->settings['googleJavascriptApiLanguage'] ? $this->settings['googleJavascriptApiLanguage'] : 'en',
                $this->settings['googleJavascriptApiKey']
            );

            $this->pageRenderer->addJsFooterLibrary('googleapis', $pathGoogleMaps);

            // include JS
            foreach ($this->settings['scripts'] as $script) {
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
    protected function getFrontendLabels() {
        /** @var LocalizationFactory $languageFactory */
        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);

        $langKey = MainUtility::getTSFE()->config['config']['language'];
        $labels = $languageFactory->getParsedData('EXT:pxa_dealers/Resources/Private/Language/locallang.xlf', $langKey ? $langKey : 'en');

        if(!empty($labels[$langKey])) {
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