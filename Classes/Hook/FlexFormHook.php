<?php

namespace Pixelant\PxaDealers\Hook;


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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Modify flexform
 *
 * @package Pixelant\PxaDealers\Hook
 */
class FlexFormHook
{

    /**
     * Fields list to remove on maps view
     *
     * @var array
     */
    public $removedFieldsInMapView = [
        'sDEF' => 'filter.mapContentElement',
    ];

    /**
     * Fields to remove if category filter selected
     *
     * @var array
     */
    public $removedFieldsInCategoriesFilterView = [
        'sDEF' => 'demand.countries',
        'map' => 'map.mapHeight,map.enableIsotope,map.markerClusterer.enable,map.markerClusterer.maxZoom'
    ];

    /**
     * Fields to remove if category filter selected
     *
     * @var array
     */
    public $removedFieldsInCategoriesCollectionFilterView = [
        'sDEF' => 'demand.countries,demand.categories,demand.orderDirection,demand.orderBy',
        'map' => 'map.mapHeight,map.enableIsotope,map.markerClusterer.enable,map.markerClusterer.maxZoom'
    ];

    /**
     * Fields to remove if countrie filter selected
     *
     * @var array
     */
    public $removedFieldsInCountriesFilterView = [
        'sDEF' => 'demand.categories,demand.orderDirection,demand.orderBy',
        'map' => 'map.mapHeight,map.enableIsotope,map.markerClusterer.enable,map.markerClusterer.maxZoom'
    ];

    /**
     * Change visible flexform fields
     *
     * @param array &$dataStructure Flexform structure
     * @param array $conf some strange configuration
     * @param array $row row of current record
     * @param string $table table name
     * @return void
     */
    public function getFlexFormDS_postProcessDS(&$dataStructure, $conf, $row, $table)
    {
        if ($table === 'tt_content' && $row['list_type'] === 'pxadealers_pxadealers' && is_array($dataStructure)) {
            $this->updateFlexforms($dataStructure, $row);
        }
    }

    /**
     * Update flexform configuration if a action is selected
     *
     * @param array|string &$dataStructure flexform structure
     * @param array $row row of current record
     * @return void
     */
    protected function updateFlexforms(array &$dataStructure, array $row)
    {
        $selectedView = '';

        // get the first selected action
        if (is_string($row['pi_flexform'])) {
            $flexformSelection = GeneralUtility::xml2array($row['pi_flexform']);
        } else {
            $flexformSelection = $row['pi_flexform'];
        }
        if (is_array($flexformSelection) && is_array($flexformSelection['data'])) {
            $selectedView = $flexformSelection['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF'];
            if (!empty($selectedView)) {
                $actionParts = GeneralUtility::trimExplode(';', $selectedView, true);
                $selectedView = $actionParts[0];
            }
            // new plugin element
        } elseif (GeneralUtility::isFirstPartOfStr($row['uid'], 'NEW')) {
            // use Map
            $selectedView = 'Dealers->map';
        }

        if (!empty($selectedView)) {
            // Modify the flexform structure depending on the first found action
            switch ($selectedView) {
                case 'Dealers->map':
                    $this->deleteFromStructure($dataStructure, $this->removedFieldsInMapView);
                    break;
                case 'Filter->categoriesFilter':
                    $this->deleteFromStructure($dataStructure, $this->removedFieldsInCategoriesFilterView);
                    break;
                case  'Filter->countriesFilter':
                    $this->deleteFromStructure($dataStructure, $this->removedFieldsInCountriesFilterView);
                    break;
                case  'Filter->categoriesCollectionFilter':
                    $this->deleteFromStructure($dataStructure, $this->removedFieldsInCategoriesCollectionFilterView);
                    break;
                default:
            }
        }
    }

    /**
     * Remove fields from flexform structure
     *
     * @param array &$dataStructure flexform structure
     * @param array $fieldsToBeRemoved fields which need to be removed
     * @return void
     */
    protected function deleteFromStructure(array &$dataStructure, array $fieldsToBeRemoved)
    {
        foreach ($fieldsToBeRemoved as $sheetName => $sheetFields) {
            $fieldsInSheet = GeneralUtility::trimExplode(',', $sheetFields, true);

            foreach ($fieldsInSheet as $fieldName) {
                unset($dataStructure['sheets'][$sheetName]['ROOT']['el']['settings.' . $fieldName]);
            }
        }
    }
}