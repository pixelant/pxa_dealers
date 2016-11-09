<?php
defined('TYPO3_MODE') or die();

$init = function($_EXTKEY) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        $_EXTKEY,
        'Pxadealers',
        'Pxa Dealers'
    );

    /* Add FlexForm */
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['pxadealers_pxadealers'] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('pxadealers_pxadealers', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/FlexForm.xml');
    /****************************************/

    # remove some fields
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['pxadealers_pxadealers'] = 'layout,select_key';

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