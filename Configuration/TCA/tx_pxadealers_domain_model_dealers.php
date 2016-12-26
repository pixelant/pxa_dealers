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

        'languageField' => 'sys_language_uid',

        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'name,title,country,telephone,website,adrress,zipcode,description,lat,lng',

        'typeicon_classes' => [
            'default' => 'ext-pxadealers-wizard-icon'
        ]
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, logo, zipcode, city, address, country, lat, lng, show_street_view, gm_position',
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;paletteLangHidden, name, logo, 
		        --div--;LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.coordinates, show_street_view, --palette--;;paletteCountry,--palette--;;paletteNavigation,--palette--;;paletteLatLng, 
		        --div--;LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.paletteAdditionalFields,--palette--;;paletteAdditionalFields,
		        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime'
        ]
    ],
    'palettes' => [
        'paletteLangHidden' => [
            'showitem' => 'hidden, sys_language_uid, l10n_parent, l10n_diffsource',
            'canNotCollapse' => false
        ],
        'paletteNavigation' => [
            'showitem' => 'address, city, zipcode, --linebreak--, gm_position',
            'canNotCollapse' => false
        ],
        'paletteCountry' => [
            'showitem' => 'country',
            'canNotCollapse' => false
        ],
        'paletteLatLng' => [
            'showitem' => 'lat, lng',
            'canNotCollapse' => false
        ],
        'paletteAdditionalFields' => [
            'showitem' => 'phone, --linebreak--, link, --linebreak--, website, --linebreak--, email, --linebreak--, description',
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
                'eval' => 'trim'
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
                            'dragdrop' => false,
                            'sort' => false,
                            'hide' => true,
                            'delete' => true,
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
                    'maxitems' => 1
                ]
            )
        ],
        'show_street_view' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.show_street_view',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ]
        ],
        'gm_position' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.gm_position',
            'config' => [
                'type' => 'user',
                'userFunc' => \Pixelant\PxaDealers\Utility\TcaUtility::class . '->renderGoogleMapPosition',
                'parameters' => [
                    'longitude' => 'lng',
                    'latitude' => 'lat',
                    'address' => 'address',
                    'zipcode' => 'zipcode',
                    'country' => 'country',
                    'city' => 'city'
                ]
            ]
        ],
        'city' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'phone' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.phone',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'website' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.website',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                        'module' => [
                            'name' => 'wizard_link',
                        ],
                        'params' => [
                            'blindLinkOptions' => 'email'
                        ],

                        'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=1'
                    ]
                ],
                'softref' => 'typolink'
            ]
        ],
        'link' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.link',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                        'module' => [
                            'name' => 'wizard_link',
                        ],
                        'params' => [
                            'blindLinkOptions' => 'email'
                        ],

                        'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=1'
                    ]
                ],
                'softref' => 'typolink'
            ]
        ],
        'email' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                        'module' => [
                            'name' => 'wizard_link',
                        ],
                        'params' => [
                            'blindLinkOptions' => 'url,spec,file,folder,page'
                        ],

                        'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=1'
                    ]
                ],
                'softref' => 'typolink'
            ]
        ],
        'description' => [
            'exclude' => 0,
            'defaultExtras' => 'richtext:rte_transform[mode=ts_css]',
            'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
                'wizards' => [
                    '_PADDING' => 2,
                    'RTE' => [
                        'notNewRecords' => 1,
                        'RTEonly' => 1,
                        'type' => 'script',
                        'title' => 'Full screen Rich Text Editing',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif',
                        'module' => [
                            'name' => 'wizard_rte',
                        ],
                    ],
                ],
            ]
        ]
    ]
];