<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PXA.' . $_EXTKEY,
	'Pxadealerssearchform',
	array(
		'Dealers' => 'searchForm',
		
	),
	// non-cacheable actions
	array(
		'Dealers' => 'searchForm',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PXA.' . $_EXTKEY,
	'Pxadealerssearchresults',
	array(
		'Dealers' => 'searchResults,import,findClosestAjax',
		
	),
	// non-cacheable actions
	array(
		'Dealers' => 'searchResults,import,findClosestAjax',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PXA.' . $_EXTKEY,
	'Pxadealerscategories',
	array(
		'Categories' => 'list',
		
	),
	// non-cacheable actions
	array(
		'Categories' => 'list',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PXA.' . $_EXTKEY,
	'Pxadealerscountries',
	array(
		'Countries' => 'list',
		
	),
	// non-cacheable actions
	array(
		'Countries' => 'list',
		
	)
);

// Hook before dealer is saved 
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['PxaDealers\\Hook\\DealersHook'] = 'EXT:pxa_dealers/Classes/Hook/PxaDealersHook.php:&PXA\\PxaDealers\\Hook\\PxaDealersHook';

// Real url
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT']['postVarSets']['_DEFAULT']['dealers-search'] = array(

	array(
		'GETvar' => 'tx_pxadealers_pxadealerssearchresults[controller]',
		'noMatch' => 'bypass',
	),
	array(
		'GETvar' => 'tx_pxadealers_pxadealerssearchresults[action]',
		'valueMap' => array(
            'search' => 'searchResults'
        )		
	),
	array(
		'GETvar' => 'tx_pxadealers_pxadealerssearchresults[searchValue]',		
	)	
);
?>