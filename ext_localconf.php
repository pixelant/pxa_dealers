<?php
defined('TYPO3_MODE') or die();

$init = function ($_EXTKEY) {
    # register icons
    if (TYPO3_MODE === 'BE') {
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            'ext-pxadealers-wizard-icon',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/wizard_icon.svg']
        );
    }
};

$init($_EXTKEY);
unset($init);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Pixelant.' . $_EXTKEY,
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
    'Pixelant.' . $_EXTKEY,
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
    'Pixelant.' . $_EXTKEY,
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
    'Pixelant.' . $_EXTKEY,
    'Pxadealerscountries',
    array(
        'Countries' => 'list',

    ),
    // non-cacheable actions
    array(
        'Countries' => 'list',

    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Pixelant.' . $_EXTKEY,
    'Pxadealersclosest',
    array(
        'Closest' => 'show',

    ),
    // non-cacheable actions
    array(
        'Closest' => 'show',
    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Pixelant.' . $_EXTKEY,
    'Pxadealersmap',
    array(
        'Dealers' => 'showMap',

    ),
    // non-cacheable actions
    array(
        'Dealers' => 'showMap',
    )
);

// Hook before dealer is saved 
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['PxaDealers\\Hook\\DealersHook'] = 'EXT:pxa_dealers/Classes/Hook/PxaDealersHook.php:&Pixelant\\PxaDealers\\Hook\\PxaDealersHook';

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

// Import task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']["Pixelant\PxaDealers\Task\ImportTask"] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_pxadealers.task.import.name',
    'description' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_pxadealers.task.import.description',
    'additionalFields' => "Pixelant\PxaDealers\Task\ImportAdditionalFieldProvider"
);

// Cleanup coordinated
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']["Pixelant\PxaDealers\Task\CleanUpCoordinatesCacheTask"] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_pxadealers.task.сleanUpCoordinatesCacheTask.name',
    'description' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:tx_pxadealers.task.CleanUpCoordinatesCacheTask.description',
    'additionalFields' => "Pixelant\PxaDealers\Task\CleanUpCoordinatesCacheAdditionalFieldProvider"
);

// Add evaluate functions

$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['Pixelant\PxaDealers\Utility\EvalFunctions\EvaluateGoogleMapsCoordinates'] = '\Pixelant\PxaDealers\Utility\EvalFunctions\EvaluateGoogleMapsCoordinates';

?>