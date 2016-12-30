<?php


namespace Pixelant\PxaDealers\Hook;

use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class PageLayoutView
{
    /**
     * Generate html (preview) for plugin in BE
     *
     * @param array $params
     * @return string
     */
    public function getInfo($params)
    {
        $info = '<strong>Pxa Dealers</strong><br>';

        $additionalInfo = '';

        if ($params['row']['list_type'] == 'pxadealers_pxadealers') {
            $flexFormData = GeneralUtility::xml2array($params['row']['pi_flexform']);

            $settings = MainUtility::flexForm2Array($flexFormData);

            $info .= $this->getSwitchableControllerActionsLabel($settings);

            $additionalInfo .= $this->getRecordsStorageInfo(GeneralUtility::intExplode(',', $params['row']['pages']));

            if ($settings['switchableControllerActions'] === 'Dealers->countriesFilter'
                || $settings['switchableControllerActions'] === 'Dealers->map'
            ) {

                $additionalInfo .= $this->getInfoFor('static_countries', 'be.all', 'cn_short_en', 'be.countries',
                    $settings['settings']['demand']['countries']);
            }

            if ($settings['switchableControllerActions'] === 'Dealers->categoriesCollectionFilter') {

                $additionalInfo .= $this->getInfoFor('tx_pxadealers_domain_model_categoriesfilteroption', 'be.any',
                    'name', 'be.categoriesFilterOptions', $settings['settings']['filter']['categoriesFilterOptions']);
            }

            if ($settings['switchableControllerActions'] === 'Dealers->categoriesFilter'
                || $settings['switchableControllerActions'] === 'Dealers->map'
            ) {

                $additionalInfo .= $this->getInfoFor('sys_category', 'be.any', 'title', 'be.categories',
                    $settings['settings']['demand']['categories']);
            }

            if ($settings['switchableControllerActions'] === 'Dealers->map') {
                $additionalInfo .= $this->getInfoOrderFields($settings);
            }

            if ($settings['switchableControllerActions'] === 'Dealers->search') {
                $additionalInfo .= $this->getInfoFor('pages', 'be.no_result', 'title',
                    'flexform.search.searchResultPage', $settings['settings']['search']['searchResultPage']);
            }

            if (GeneralUtility::inList('Dealers->categoriesFilter,Dealers->categoriesCollectionFilter,Dealers->countriesFilter',
                $settings['switchableControllerActions'])
            ) {
                $additionalInfo .= $this->getInfoFor('tt_content', 'be.no_result', 'header',
                    'be.mapContentElement', $settings['settings']['filter']['mapContentElement']);
            }
        }

        return $info . ($additionalInfo ? '<hr><pre>' . $additionalInfo . '</pre>' : '');
    }

    /**
     * Generate label for switchable controller action
     *
     * @param array $settings
     * @return string
     */
    protected function getSwitchableControllerActionsLabel(array $settings)
    {
        list(, $actionName) = GeneralUtility::trimExplode('->', $settings['switchableControllerActions']);

        return sprintf('<strong>%s: %s</strong>', MainUtility::translate('flexform.actions.mode'),
            MainUtility::translate('flexform.actions.' . $actionName));
    }

    /**
     * Get info about storage
     *
     * @param array $pages
     * @return string
     */
    protected function getRecordsStorageInfo(array $pages)
    {
        $storages = [];

        foreach ($pages as $page) {
            // Select UID version:
            $row = BackendUtility::getRecord('pages', $page, 'title');
            // Add rows to output array:
            if ($row) {
                $storages[] = sprintf('%s [%d]', $row['title'], $page);
            }
        }

        if (!empty($storages)) {
            return sprintf('<b>%s</b>: %s<br>', MainUtility::translate('be.recordStorage'), implode(', ', $storages));
        } else {
            return '';
        }
    }

    /**
     * Generate info for list of records
     *
     * @param $table
     * @param $noResult
     * @param $field
     * @param $for
     * @param string $settingsField
     * @return string
     */
    protected function getInfoFor($table, $noResult, $field, $for, $settingsField)
    {
        if (!empty($settingsField)) {
            $rows = MainUtility::getDatabaseConnection()->exec_SELECTgetRows(
                'uid,' . $field,
                $table,
                'uid IN (' . $settingsField . ')'
            );

            $countries = [];

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $countries[] = (empty($row[$field]) ? MainUtility::translate('be.empty') : $row[$field]) . ' [' . $row['uid'] . ']';
                }

                return sprintf('<b>%s</b>: %s<br>', MainUtility::translate($for), implode(', ', $countries));
            }
        }

        return sprintf('<b>%s</b>: %s<br>', MainUtility::translate($for), MainUtility::translate($noResult));
    }


    /**
     * Info about direction fields
     *
     * @param array $settings
     * @return string
     */
    protected function getInfoOrderFields(array $settings)
    {
        $output = sprintf('<b>%s</b>: %s<br>', MainUtility::translate('flexform.demand.orderBy'),
            MainUtility::translate('flexform.demand.orderBy.' . $settings['settings']['demand']['orderBy']));
        $output .= sprintf('<b>%s</b>: %s<br>', MainUtility::translate('flexform.demand.orderDirection'),
            MainUtility::translate('flexform.demand.orderDirection.' . $settings['settings']['demand']['orderDirection']));

        return $output;
    }
}