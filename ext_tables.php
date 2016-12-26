<?php
defined('TYPO3_MODE') or die();

$init = function($_EXTKEY) {
    # Add static template
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Pxa Dealers');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_pxadealers_domain_model_dealers', 'EXT:pxa_dealers/Resources/Private/Language/locallang_csh_tx_pxadealers_domain_model_dealers.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pxadealers_domain_model_dealers');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_pxadealers_domain_model_categoriesfilteroption', 'EXT:pxa_dealers/Resources/Private/Language/locallang_csh_tx_pxadealers_domain_model_categoriesfilteroption.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pxadealers_domain_model_categoriesfilteroption');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        $_EXTKEY,
        'tx_pxadealers_domain_model_dealers',
        // Do not use the default field name ("categories"), which is already used
        // Also do not use a field name containing "categories" (see http://forum.typo3.org/index.php/t/199595/)
        'categories'
    );
};

$init($_EXTKEY);
unset($init);