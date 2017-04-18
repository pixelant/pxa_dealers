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
        'sDEF' => 'search.searchResultPage',
    ];

    // @codingStandardsIgnoreStart
    /**
     * Fields to remove if category filter selected
     *
     * @var array
     */
    public $removedFieldsInCategoriesFilterView = [
        'sDEF' => 'search.searchResultPage,demand.countries',
        'map' => 'map.mapHeight,map.markerClusterer.enable,map.markerClusterer.maxZoom'
    ];

    /**
     * Fields to remove if countries filter selected
     *
     * @var array
     */
    public $removedFieldsInCountriesFilterView = [
        'sDEF' => 'search.searchResultPage,demand.categories,demand.orderDirection,demand.orderBy',
        'map' => 'map.mapHeight,map.markerClusterer.enable,map.markerClusterer.maxZoom'
    ];

    /**
     * Fields to remove if search was selected
     *
     * @var array
     */
    public $removedFieldsInSearchView = [
        'sDEF' => 'demand.categories,demand.countries,demand.orderDirection,demand.orderBy',
        'map' => 'map.mapHeight,map.markerClusterer.enable,map.markerClusterer.maxZoom'
    ];

    public $removedFieldsInSearchResults = [
        'sDEF' => 'search.searchResultPage,demand.categories,demand.countries',
        'map' => 'map.mapHeight,map.markerClusterer.enable,map.markerClusterer.maxZoom'
    ];

    /**
     * The data structure depends on a current form selection (persistenceIdentifier)
     * and if the field "overrideFinishers" is active. Add both to the identifier to
     * hand these information over to parseDataStructureByIdentifierPostProcess() hook.
     *
     * @param array $fieldTca Incoming field TCA
     * @param string $tableName Handled table
     * @param string $fieldName Handled field
     * @param array $row Current data row
     * @param array $identifier Already calculated identifier
     * @return array Modified identifier
     */
    public function getDataStructureIdentifierPostProcess(
        array $fieldTca,
        string $tableName,
        string $fieldName,
        array $row,
        array $identifier
    ): array {
        if ($tableName === 'tt_content' && $fieldName === 'pi_flexform' && $row['list_type'] === 'pxadealers_pxadealers') {
            $currentFlexData = [];
            if (!is_array($row['pi_flexform']) && !empty($row['pi_flexform'])) {
                $currentFlexData = GeneralUtility::xml2array($row['pi_flexform']);
            }

            if (isset($currentFlexData['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF'])
                && !empty($currentFlexData['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF'])
            ) {
                $selectedView = $currentFlexData['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF'];

                $actionParts = GeneralUtility::trimExplode(';', $selectedView, true);
                $selectedView = $actionParts[0];
                // new plugin element
            } else {
                // use Map
                $selectedView = 'Dealers->map';
            }
            // save it for parseDataStructureByIdentifierPostProcess
            $identifier['ext-pxa-dealers-switchableControllerActions'] = $selectedView;
        }

        return $identifier;
    }

    /**
     * Change visible flexform fields
     * Used in TYPO3 > 8
     *
     * @param array $dataStructure
     * @param array $identifier
     * @return array
     */
    public function parseDataStructureByIdentifierPostProcess($dataStructure, $identifier)
    {
        if ($identifier['dataStructureKey'] === 'pxadealers_pxadealers,list'
            && $identifier['fieldName'] === 'pi_flexform'
            && isset($identifier['ext-pxa-dealers-switchableControllerActions'])
        ) {
            $this->updateFlexforms($dataStructure, $identifier['ext-pxa-dealers-switchableControllerActions']);
        }

        return $dataStructure;
    }

    /**
     * Change visible flexform fields
     * Used in TYPO3 < 8
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
            // get the first selected action
            if (is_string($row['pi_flexform'])) {
                $flexFormSelection = GeneralUtility::xml2array($row['pi_flexform']);
            } else {
                $flexFormSelection = $row['pi_flexform'];
            }
            if (is_array($flexFormSelection) && is_array($flexFormSelection['data'])) {
                $selectedView = $flexFormSelection['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF'];

                $actionParts = GeneralUtility::trimExplode(';', $selectedView, true);
                $selectedView = $actionParts[0];

                // new plugin element
            } elseif (GeneralUtility::isFirstPartOfStr($row['uid'], 'NEW')) {
                // use Map
                $selectedView = 'Dealers->map';
            }

            $this->updateFlexforms($dataStructure, $selectedView);
        }
    }
    // @codingStandardsIgnoreEnd

    /**
     * Update flexform configuration if a action is selected
     *
     * @param array|string &$dataStructure flexform structure
     * @param array $row row of current record
     * @return void
     */
    protected function updateFlexforms(array &$dataStructure, string $selectedView)
    {
        // Modify the flexform structure depending on the first found action
        switch ($selectedView) {
            case 'Dealers->map':
                $this->deleteFromStructure($dataStructure, $this->removedFieldsInMapView);
                break;
            case 'Categories->categoriesFilter':
                $this->deleteFromStructure($dataStructure, $this->removedFieldsInCategoriesFilterView);
                break;
            case 'Countries->countriesFilter':
                $this->deleteFromStructure($dataStructure, $this->removedFieldsInCountriesFilterView);
                break;
            case 'Search->search':
                $this->deleteFromStructure($dataStructure, $this->removedFieldsInSearchView);
                break;
            case 'Search->searchResults':
                $this->deleteFromStructure($dataStructure, $this->removedFieldsInSearchResults);
                break;
            default:
                $this->deleteFromStructure($dataStructure, $this->removedFieldsInMapView);
                break;
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
