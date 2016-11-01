<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers',
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
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'name,title,country,telephone,website,adrress,zipcode,description,lat,lng,lat_lng_is_set',
        'requestUpdate' => 'country',
        'iconfile' => 'EXT:pxa_dealers/Resources/Public/Icons/tx_pxadealers_domain_model_dealers.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, logo, zipcode, address, country, country_zone, lat, lng, show_street_view, gm_position',
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;paletteLangHidden, --palette--;;paletteMain, 
		        --div--;LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.coordinates,--palette--;;paletteCountry,--palette--;;paletteNavigation,--palette--;;paletteLatLng, 
		        --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime'
        ]
    ],
    'palettes' => [
        'paletteLangHidden' => [
            'showitem' => 'hidden, sys_language_uid, l10n_parent, l10n_diffsource',
            'canNotCollapse' => false
        ],
        'paletteMain' => [
            'showitem' => 'name, --linebreak--, logo',
            'canNotCollapse' => false
        ],
        'paletteNavigation' => [
            'showitem' => 'address, zipcode, --linebreak--, gm_position',
            'canNotCollapse' => false
        ],
        'paletteCountry' => [
            'showitem' => 'country, country_zone',
            'canNotCollapse' => false
        ],
        'paletteLatLng' => [
            'showitem' => 'lat, lng',
            'canNotCollapse' => false
        ]
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_pxadealers_domain_model_dealers',
                'foreign_table_where' => 'AND tx_pxadealers_domain_model_dealers.pid=###CURRENT_PID### AND tx_pxadealers_domain_model_dealers.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255
            ]
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'country' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.country',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'static_countries',
                'foreign_table_where' => 'ORDER BY static_countries.cn_short_en',
                'size' => 1,
                'autoSizeMax' => 30,
                'maxitems' => 1,
                'multiple' => 0,
                'wizards' => [
                    '_POSITION' => 'top',
                    'suggest' => [
                        'type' => 'suggest'
                    ],
                ],
            ],
        ],
        'country_zone' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.country_zone',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'static_country_zones',
                'foreign_table_where' => 'AND static_country_zones.zn_country_uid = ###REC_FIELD_country### ORDER BY static_country_zones.uid',
                'items' => [
                    ['LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.country_zone.none', 0]
                ],
                'disableNoMatchingValueElement' => 1,
                'size' => 1,
                'autoSizeMax' => 30,
                'maxitems' => 1,
                'multiple' => 0,
                'default' => 0,
                'wizards' => [
                    '_POSITION' => 'top',
                    'suggest' => [
                        'type' => 'suggest'
                    ],
                ],
            ],
        ],
        'address' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.address',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'zipcode' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.zipcode',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim'
            ],
        ],

        'zipcode_search' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.zipcode_search',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],

        'lat' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.lat',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim'
            ],
        ],
        'lng' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.lng',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim'
            ],
        ],

        'logo' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.logo',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'attachment',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:media.addFileReference',
                        'enabledControls' => [
                            'info' => true,
                            'new' => false,
                            'dragdrop' => true,
                            'sort' => false,
                            'hide' => true,
                            'delete' => false,
                            'localize' => false,
                        ]
                    ],
                    'foreign_types' => [
                        '0' => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ]
                    ],
                    'maxitems' => 99
                ]
            )
        ],
        'show_street_view' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.show_street_view',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ],
        ],
        'gm_position' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.gm_position',
            'config' => [
                'type' => 'user',
                'userFunc' => \Pixelant\PxaDealers\Utility\TcaUtility::class . '->renderGoogleMapPosition'
            ],
        ],
    ],
];