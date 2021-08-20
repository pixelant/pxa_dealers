<?php

declare(strict_types=1);

namespace Pixelant\PxaDealers\Hook;

use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class PageLayoutView
{
    /**
     * Generate html (preview) for plugin in BE.
     *
     * @param array $params
     * @return string
     */
    public function getInfo(array $params): string
    {
        $info = '<strong>Pxa Dealers</strong><br>';

        $additionalInfo = '';

        if ($params['row']['list_type'] === 'pxadealers_pxadealers') {
            $settings = GeneralUtility::makeInstance(FlexFormService::class)->convertFlexFormContentToArray(
                $params['row']['pi_flexform']
            );

            [, $actionName] = GeneralUtility::trimExplode(
                '->',
                GeneralUtility::trimExplode(
                    ';',
                    $settings['switchableControllerActions']
                )[0]
            );

            $info .= $this->getSwitchableControllerActionsLabel($actionName);

            $additionalInfo .= $this->getRecordsStorageInfo(GeneralUtility::intExplode(',', $params['row']['pages']));

            if (
                $actionName === 'countriesFilter'
                || $actionName === 'map'
            ) {
                $additionalInfo .= $this->getInfoFor(
                    'static_countries',
                    'be.all',
                    'cn_short_en',
                    'be.countries',
                    $settings['settings']['demand']['countries']
                );
            }

            if (
                $actionName === 'categoriesFilter'
                || $actionName === 'map'
            ) {
                $additionalInfo .= $this->getInfoFor(
                    'sys_category',
                    'be.any',
                    'title',
                    'be.categories',
                    $settings['settings']['demand']['categories']
                );
                $additionalInfo .= $this->getInfoOrderFields($settings);
            }

            if ($actionName === 'form') {
                $additionalInfo .= $this->getInfoFor(
                    'pages',
                    'be.no_result',
                    'title',
                    'flexform.search.searchResultPage',
                    (string)$settings['settings']['search']['searchResultPage']
                );
                $additionalInfo .= $this->getCheckBoxInfo(
                    $settings['settings']['search']['searchInRadius'],
                    'be.searchInRadius'
                );
                $additionalInfo .= $this->getCheckBoxInfo(
                    $settings['settings']['search']['searchClosest'],
                    'be.searchClosest'
                );
            }
        }

        return $info . ($additionalInfo ? '<hr><pre>' . $additionalInfo . '</pre>' : '');
    }

    /**
     * Generate label for switchable controller action.
     *
     * @param string $actionName
     * @return string
     */
    protected function getSwitchableControllerActionsLabel(string $actionName): string
    {
        return sprintf(
            '<strong>%s: <i>%s</i></strong>',
            MainUtility::translate('flexform.actions.mode'),
            MainUtility::translate('flexform.actions.' . $actionName)
        );
    }

    /**
     * Get info about storage.
     *
     * @param array $pages
     * @return string
     */
    protected function getRecordsStorageInfo(array $pages): string
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
        }

        return '';
    }

    /**
     * Generate info for list of records.
     *
     * @param string $table
     * @param string $noResult
     * @param string $field
     * @param string $for
     * @param string $settingsField
     * @return string
     */
    protected function getInfoFor(
        string $table,
        string $noResult,
        string $field,
        string $for,
        string $settingsField
    ): string {
        if (!empty($settingsField)) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);

            $statement = $queryBuilder
                ->select('uid', $field)
                ->from($table)
                ->where(
                    $queryBuilder->expr()->in(
                        'uid',
                        GeneralUtility::intExplode(',', $settingsField, Connection::PARAM_INT_ARRAY)
                    )
                )
                ->execute();

            $lines = [];
            foreach ($statement->fetchAll() as $row) {
                $line = empty($row[$field]) ? MainUtility::translate('be.empty') : $row[$field];
                $lines[] = $line . ' [' . $row['uid'] . ']';
            }
            if (!empty($lines)) {
                return sprintf('<b>%s</b>: %s<br>', MainUtility::translate($for), implode(', ', $lines));
            }
        }

        return sprintf('<b>%s</b>: %s<br>', MainUtility::translate($for), MainUtility::translate($noResult));
    }

    /**
     * Info about direction fields.
     *
     * @param array $settings
     * @return string
     */
    protected function getInfoOrderFields(array $settings): string
    {
        $output = sprintf(
            '<b>%s</b>: %s<br>',
            MainUtility::translate('flexform.demand.orderBy'),
            MainUtility::translate('flexform.demand.orderBy.' . $settings['settings']['demand']['orderBy'])
        );

        $output .= sprintf(
            '<b>%s</b>: %s<br>',
            MainUtility::translate('flexform.demand.orderDirection'),
            MainUtility::translate(
                'flexform.demand.orderDirection.' . $settings['settings']['demand']['orderDirection']
            )
        );

        return $output;
    }

    /**
     * Get description for checkbox.
     *
     * @param $value
     * @param $label
     * @return string
     */
    protected function getCheckBoxInfo($value, $label): string
    {
        return sprintf(
            '<b>%s</b>: %s<br>',
            MainUtility::translate($label),
            MainUtility::translate($value ? 'be.yes' : 'be.no')
        );
    }
}
