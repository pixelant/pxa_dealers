<?php
defined('TYPO3_MODE') or die();

call_user_func(
    function () {
        # register plugin
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Pixelant.pxa_dealers',
            'Pxadealers',
            [
                'Dealers' => 'map, search',
                'Categories' => 'categoriesFilter',
                'Countries' => 'countriesFilter',
                'Search' => 'form, suggest'
            ],
            // non-cacheable actions
            [
                'Dealers' => 'search',
                'Search' => 'form, suggest'
            ]
        );

        // @codingStandardsIgnoreStart
        // Hook for flexform processing
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\\CMS\\Core\\Configuration\\FlexForm\FlexFormTools']['flexParsing']['pxa_dealers'] = \Pixelant\PxaDealers\Hook\FlexFormHook::class;
        // @codingStandardsIgnoreEnd

        // Register a node.
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1604915385] = [
            'nodeName' => 'pxaDealersGoogleMaps',
            'priority' => 40,
            'class' => \Pixelant\PxaDealers\Form\Element\GoogleMapsElement::class,
        ];

        // Add page TS
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '@import \'EXT:pxa_dealers/Configuration/PageTSconfig/ContentElementWizard.ts\''
        );

        # register icons
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );

        $iconRegistry->registerIcon(
            'ext-pxadealers-wizard-icon',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:pxa_dealers/Resources/Public/Icons/wizard_icon.svg']
        );

        // hook for extension BE view
        // @codingStandardsIgnoreStart
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['pxadealers_pxadealers']['pxa_dealers'] = \Pixelant\PxaDealers\Hook\PageLayoutView::class . '->getInfo';
        // @codingStandardsIgnoreEnd

        // Caching for results from google api
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['pxa_dealers'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['pxa_dealers'] = [
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
                'options' => [
                    'defaultLifetime' => 3600 * 24 * 7
                ]
            ];
        }
    }
);
