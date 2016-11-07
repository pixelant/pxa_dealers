<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pxadealerssearchform',
	'Pxa Dealers Search Form'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pxadealerssearchresults',
	'Pxa Dealers Results'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pxadealerscategories',
	'Pxa Dealers Categories'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pxadealerscountries',
	'Pxa Dealers Countries'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pxadealersclosest',
	'Pxa Dealers: Find Closest'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pxadealersmap',
	'Pxa Dealers: Map'
);

/* Add FlexForm */

$pluginSignature = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature . '_pxadealerssearchform'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature . '_pxadealerssearchform', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_search_form.xml');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature . '_pxadealerssearchresults'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature . '_pxadealerssearchresults', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_search_result.xml');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature . '_pxadealerscategories'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature . '_pxadealerscategories', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_categories.xml');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature . '_pxadealerscountries'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature . '_pxadealerscountries', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_countries.xml');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature . '_pxadealersmap'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature . '_pxadealersmap', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_map.xml');
/****************************************/

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Pxa Dealers');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_pxadealers_domain_model_dealers', 'EXT:pxa_dealers/Resources/Private/Language/locallang_csh_tx_pxadealers_domain_model_dealers.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pxadealers_domain_model_dealers');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        $_EXTKEY,
        'tx_pxadealers_domain_model_dealers',
        // Do not use the default field name ("categories"), which is already used
        // Also do not use a field name containing "categories" (see http://forum.typo3.org/index.php/t/199595/)
        'categories'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_pxadealers_domain_model_categoriesfilteroption', 'EXT:pxa_dealers/Resources/Private/Language/locallang_csh_tx_pxadealers_domain_model_categoriesfilteroption.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pxadealers_domain_model_categoriesfilteroption');

?>