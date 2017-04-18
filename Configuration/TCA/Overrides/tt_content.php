<?php

defined('TYPO3_MODE') or die();

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['pxadealers_pxadealers'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'pxadealers_pxadealers',
    'FILE:EXT:pxa_dealers/Configuration/FlexForms/FlexForm.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'pxa_dealers',
    'Pxadealers',
    'Pxa Dealers'
);

# remove some fields
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['pxadealers_pxadealers'] = 'layout,select_key';
