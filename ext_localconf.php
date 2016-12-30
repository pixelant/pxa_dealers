<?php
defined('TYPO3_MODE') or die();

$init = function ($_EXTKEY) {

    # register plugin
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Pixelant.' . $_EXTKEY,
        'Pxadealers',
        [
            'Dealers' => 'map, search, categoriesFilter, countriesFilter, categoriesCollectionFilter'
        ],
        // non-cacheable actions
        [

        ]
    );

    // Hook before dealer is saved
    $GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$_EXTKEY] = \Pixelant\PxaDealers\Hook\PxaDealersHook::class;

    // Hook for flexform processing
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][$_EXTKEY] = \Pixelant\PxaDealers\Hook\FlexFormHook::class;

    // Real url
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')) {
        $GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['postVarSets']['_DEFAULT']['dealers-search'] = [
            [
                'GETvar' => 'tx_pxadealers_pxadealerssearchresults[controller]',
                'noMatch' => 'bypass',
            ],
            [
                'GETvar' => 'tx_pxadealers_pxadealerssearchresults[action]',
                'valueMap' => [
                    'search' => 'searchResults'
                ]
            ],
            [
                'GETvar' => 'tx_pxadealers_pxadealerssearchresults[searchValue]',
            ]
        ];
    }

    // Add page TS
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:pxa_dealers/Configuration/PageTSconfig/ContentElementWizard.ts">');

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

    // hook for extension BE view
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['pxadealers_pxadealers'][$_EXTKEY] = \Pixelant\PxaDealers\Hook\PageLayoutView::class . '->getInfo';
};

$init($_EXTKEY);
unset($init);


