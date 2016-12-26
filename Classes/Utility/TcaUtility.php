<?php
/**
 * Created by PhpStorm.
 * User: anjey
 * Date: 01.11.16
 * Time: 10:29
 */

namespace Pixelant\PxaDealers\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;


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
     * Custom map element
     *
     * @param array $PA
     * @param $pObj
     * @return string
     */
    public function renderGoogleMapPosition(array $PA, $pObj)
    {
        if ($PA['row']['pid'] < 0) {
            // then "Save and create new was clicked"
            $pid = BackendUtility::getRecord('tx_pxadealers_domain_model_dealers', abs($PA['row']['pid']),
                'pid')['pid'];
        } else {
            $pid = $PA['row']['pid'];
        }

        $settings = $this->loadTS($pid);

        $outPut = '';

        if ($settings['map']['googleJavascriptApiKey'] && $settings['beMainJs']) {
            $outPut .= $this->getHtml($PA);
            $outPut .= $this->getJsConfiguration($PA);

            $pathGoogleMaps = 'https://maps.googleapis.com/maps/api/js?callback=initBEMap&key=' . $settings['map']['googleJavascriptApiKey'];
            $pathMainBEJs = '/' . PathUtility::stripPathSitePrefix(GeneralUtility::getFileAbsFileName($settings['beMainJs']));

            $outPut .= '<script src="' . $pathMainBEJs . '"></script>';
            $outPut .= '<script src="' . $pathGoogleMaps . '"></script>';


        } else {
            $outPut .= '<b>' . $this->translate('tca_be_map.noApiKey') . '</b>';
        }

        return $outPut;
    }

    /**
     * Get main JS configuration
     *
     * @param $PA
     * @return string
     */
    protected function getJsConfiguration($PA)
    {

        $lat = (float)$PA['row'][$PA['parameters']['latitude']];
        $lng = (float)$PA['row'][$PA['parameters']['longitude']];
        if (!($lat && $lng)) {
            $lat = 0;
            $lng = 0;
        }

        $dataPrefix = 'data[' . $PA['table'] . '][' . $PA['row']['uid'] . ']';

        $js = <<<EOT
(function(w){
var document = w.document,
    PxaDealersMaps = w.PxaDealersMaps || {};
   
PxaDealersMaps.BEConfiguration = {
    lat: {$lat},
    lng: {$lng},
    baseId: '{$PA['itemFormElID']}',
    zoom: ({$lat} + $lng) == 0 ? 1 : 8,
    fieldPrefixName: '{$dataPrefix}',
    tableName: '{$PA['table']}',
    recordUid: '{$PA['row']['uid']}',
    longitudeField: '{$PA['parameters']['longitude']}',
    latitudeField: '{$PA['parameters']['latitude']}',
    countryField: '{$PA['parameters']['country']}',
    zipcodeField: '{$PA['parameters']['zipcode']}',
    addressField: '{$PA['parameters']['address']}',
    cityField: '{$PA['parameters']['city']}'
};

w.PxaDealersMaps = PxaDealersMaps;
})(window);
EOT;

        return '<script>' . $js . '</script>';
    }

    /**
     * Generate main html
     *
     * @param $PA
     * @return string
     */
    protected function getHtml($PA)
    {
        $baseElementId = $PA['itemFormElID'];
        $mapId = $baseElementId . '_map';
        $mapWrapper = $baseElementId . '_wrapper';
        $toolTip = $this->translate('tca_be_map.tooltip');
        $buttonText = $this->translate('tca_be_map.buttonText');

        $htmlTemplate = <<<EOT
<div id="element-wrapper-{$mapWrapper}">
    <p style="margin-bottom: 10px; padding: 15px;" class="bg-info">{$toolTip}</p>
    <input type="button" class="btn btn-info" onclick="PxaDealersMaps.BE.getAddressLatLng();return false;" value="$buttonText">
    <div id="$mapId" style="margin: 20px 0;width: 600px;height: 400px;"></div>
</div>
EOT;

        return $htmlTemplate;
    }

    /**
     * @param string $label
     * @return string
     */
    protected function translate($label)
    {
        if ($label) {
            return $this->getLang()->sL('LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:' . $label);
        }

        return '';
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    public function getLang()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Get Typoscript configuration
     *
     * @param $pageUid
     * @return array
     */
    protected function loadTS($pageUid)
    {
        $settings = [];

        /** @var PageRepository $sysPageObj */
        $sysPageObj = GeneralUtility::makeInstance(PageRepository::class);
        $rootLine = $sysPageObj->getRootLine($pageUid);

        /** @var ExtendedTemplateService $TSObj */
        $TSObj = GeneralUtility::makeInstance(ExtendedTemplateService::class);

        $TSObj->tt_track = 0;
        $TSObj->init();
        $TSObj->runThroughTemplates($rootLine);
        $TSObj->generateConfig();

        if ($TSObj->setup['plugin.']['tx_pxadealers.']['settings.']) {
            return GeneralUtility::removeDotsFromTS($TSObj->setup['plugin.']['tx_pxadealers.']['settings.']);
        }

        return $settings;
    }
}