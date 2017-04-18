<?php

defined('TYPO3_MODE') or die();

call_user_func(
    function ($EXTKEY) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
            'tx_pxadealers_domain_model_dealer',
            'EXT:pxa_dealers/Resources/Private/Language/locallang_csh_tx_pxadealers_domain_model_dealers.xlf'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
            'tx_pxadealers_domain_model_dealer'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
            $EXTKEY,
            'tx_pxadealers_domain_model_dealer',
            // Do not use the default field name ("categories"), which is already used
            // Also do not use a field name containing "categories" (see http://forum.typo3.org/index.php/t/199595/)
            'dealers_categories'
        );
    },
    'pxa_dealers'
);
