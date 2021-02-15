<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/***************************************************************
 *  Copyright notice
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
class TcaUtility
{
    /**
     * Get where clause for categories
     *
     * @return string
     */
    public function getCategoriesPidRestriction(): string
    {
        return $this->getForeignTableWhereRestriction('sys_category');
    }

    /**
     * Custom map element
     *
     * @param array $PA
     * @return string
     */
    public function renderGoogleMapPosition(array $PA)
    {
        if ($PA['row']['pid'] < 0) {
            // then "Save and create new was clicked"
            $pid = BackendUtility::getRecord(
                'tx_pxadealers_domain_model_dealer',
                abs($PA['row']['pid']),
                'pid'
            )['pid'];
        } else {
            $pid = $PA['row']['pid'];
        }

        $settings = $this->loadTS($pid);

        $outPut = '';

        if ($settings['map']['googleJavascriptApiKey']) {
            $outPut .= $this->getHtml($PA);

            $this->loadRequireJsWithConfiguration(
                $PA,
                $settings['map']['googleJavascriptApiKey']
            );
        } else {
            $outPut .= '<b>' . MainUtility::translate('tca_be_map.noApiKey') . '</b>';
        }

        return $outPut;
    }

    /**
     * Get main JS configuration
     *
     * @param array $PA
     * @param string $key
     */
    protected function loadRequireJsWithConfiguration(array $PA, string $key)
    {
        $lat = (float)$PA['row'][$PA['parameters']['latitude']];
        $lng = (float)$PA['row'][$PA['parameters']['longitude']];

        if (!($lat && $lng)) {
            $lat = 0;
            $lng = 0;
        }

        $dataPrefix = 'data[' . $PA['table'] . '][' . $PA['row']['uid'] . ']';
        $jsConfigurationObject = [
            'lat' => $lat,
            'lng' => $lng,
            'baseId' => $PA['itemFormElID'],
            'zoom' => ($lat + $lng) == 0 ? 1 : 8,
            'fieldPrefixName' => $dataPrefix,
            'tableName' => $PA['table'],
            'recordUid' => $PA['row']['uid'],
            'longitudeField' => $PA['parameters']['longitude'],
            'latitudeField' => $PA['parameters']['latitude'],
            'countryField' => $PA['parameters']['country'],
            'zipcodeField' => $PA['parameters']['zipcode'],
            'addressField' => $PA['parameters']['address'],
            'cityField' => $PA['parameters']['city']
        ];

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/PxaDealers/Backend/DealersMapPoints', implode(LF, [
            'function (DealersMapPoints) {',
            '   window.TYPO3.DealersMapPoints_APP = DealersMapPoints.getInstance(',
            '       \'' . $key . '\',',
            '       ' . json_encode($jsConfigurationObject),
            '   ).init();',
            '}'
        ]));
    }

    /**
     * Generate main html
     *
     * @param array $PA
     * @return string
     */
    static function getHtml(array $PA)
    {
        $baseElementId = $PA['itemFormElID'];
        $mapId = $baseElementId . '_map';
        $mapWrapper = $baseElementId . '_wrapper';
        $toolTip = MainUtility::translate('tca_be_map.tooltip');
        $buttonText = MainUtility::translate('tca_be_map.buttonText');
// @codingStandardsIgnoreStart
        $htmlTemplate = <<<EOT
<div id="element-wrapper-{$mapWrapper}">
    <p style="margin-bottom: 10px; padding: 15px;" class="bg-info">{$toolTip}</p>
    <input type="button" class="btn btn-info" onclick="TYPO3.DealersMapPoints_APP.getAddressLatLng();return false;" value="$buttonText">
    <div id="$mapId" style="margin: 20px 0;width: 600px;height: 400px;"></div>
</div>
EOT;
// @codingStandardsIgnoreEnd
        return $htmlTemplate;
    }

    /**
     * Get Typoscript configuration
     *
     * @param int $pageUid
     * @return array
     */
    public function loadTS($pageUid)
    {
        $settings = [];

        try {
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        } catch (RootLineException $e) {
            $rootLine = [];
        }

        /** @var ExtendedTemplateService $TSObj */
        $TSObj = GeneralUtility::makeInstance(ExtendedTemplateService::class);
        $TSObj->tt_track = 0;
        $TSObj->runThroughTemplates($rootLine);
        $TSObj->generateConfig();

        if ($TSObj->setup['plugin.']['tx_pxadealers.']['settings.']) {
            return GeneralUtility::removeDotsFromTS($TSObj->setup['plugin.']['tx_pxadealers.']['settings.']);
        }

        return $settings;
    }

    /**
     * Generate dynamic foreign table where
     *
     * @param $table
     * @return string
     */
    protected function getForeignTableWhereRestriction(string $table): string
    {
        $categoryPid = GeneralUtility::makeInstance(ConfigurationManager::class)
            ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)['plugin.']['tx_pxadealers.']['settings.']['categoryPid'];

        if ($categoryPid) {
            $foreignTableWhere = ' AND ' . $table . '.pid='.$categoryPid.' ';
        } else {
            $foreignTableWhere = '';
        }

        return $foreignTableWhere;
    }
}
