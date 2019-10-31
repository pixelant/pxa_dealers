<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'pxa_dealers',
    'tx_pxadealers_domain_model_dealer',
    'categories',
    [
        'fieldConfiguration' => [
            'foreign_table_where' => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Pixelant\PxaDealers\Utility\TcaUtility::class)
                    ->getCategoriesPidRestriction() . 'AND sys_category.sys_language_uid IN (-1, 0)'
        ]
    ]
);
