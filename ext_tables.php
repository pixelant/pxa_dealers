<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

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
/****************************************/

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Pxa Dealers');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_pxadealers_domain_model_dealers', 'EXT:pxa_dealers/Resources/Private/Language/locallang_csh_tx_pxadealers_domain_model_dealers.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pxadealers_domain_model_dealers');
$TCA['tx_pxadealers_domain_model_dealers'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'name,title,country,telephone,website,adrress,zipcode,description,lat,lng,lat_lng_is_set',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Dealers.php',
		'requestUpdate' => 'country',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_pxadealers_domain_model_dealers.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        $_EXTKEY,
        'tx_pxadealers_domain_model_dealers',
        // Do not use the default field name ("categories"), which is already used
        // Also do not use a field name containing "categories" (see http://forum.typo3.org/index.php/t/199595/)
        'categories'
);

?>