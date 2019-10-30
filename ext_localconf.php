<?php
defined('TYPO3_MODE') or die();

call_user_func(
    function ($_EXTKEY) {
        # register plugin
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Pixelant.' . $_EXTKEY,
            'Pxadealers',
            [
                'Dealers' => 'map',
                'Categories' => 'categoriesFilter',
                'Countries' => 'countriesFilter',
                'Search' => 'search, suggest'
            ],
            // non-cacheable actions
            [
                'Search' => 'searchResults, suggest'
            ]
        );

        // @codingStandardsIgnoreStart
        // Hook for flexform processing
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\\CMS\\Core\\Configuration\\FlexForm\FlexFormTools']['flexParsing'][$_EXTKEY] = \Pixelant\PxaDealers\Hook\FlexFormHook::class;
        // @codingStandardsIgnoreEnd

        // Add page TS
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:pxa_dealers/Configuration/PageTSconfig/ContentElementWizard.ts">'
        );

        # register icons
        if (TYPO3_MODE === 'BE') {
            /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
            $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Imaging\IconRegistry::class
            );

            $iconRegistry->registerIcon(
                'ext-pxadealers-wizard-icon',
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                ['source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/wizard_icon.svg']
            );
        }

        // hook for extension BE view
        // @codingStandardsIgnoreStart
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['pxadealers_pxadealers'][$_EXTKEY] = \Pixelant\PxaDealers\Hook\PageLayoutView::class . '->getInfo';
        // @codingStandardsIgnoreEnd

        // Caching for results from google api
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$_EXTKEY])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$_EXTKEY] = [
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
                'options' => [
                    'defaultLifetime' => 3600 * 24 * 7
                ]
            ];
        }
    },
    'pxa_dealers'
);
