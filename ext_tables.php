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
    },
    'pxa_dealers'
);
