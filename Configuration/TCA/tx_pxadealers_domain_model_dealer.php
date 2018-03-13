<?php

defined('TYPO3_MODE') || die('Access denied.');

$ll = 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_pxadealers_domain_model_dealer',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,

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
    // @codingStandardsIgnoreStart
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, logo, zipcode, city, address, country, lat, lng, show_street_view, gm_position',
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;paletteLangHidden, name, logo, 
		        --div--;' . $ll . 'tx_pxadealers_domain_model_dealers.coordinates, show_street_view, --palette--;;paletteCountry,--palette--;;paletteNavigation,--palette--;;paletteLatLng, 
		        --div--;' . $ll . 'tx_pxadealers_domain_model_dealers.paletteAdditionalFields,--palette--;;paletteAdditionalFields,
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
    // @codingStandardsIgnoreEnd
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0
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
                'foreign_table' => 'tx_pxadealers_domain_model_dealer',
                // @codingStandardsIgnoreStart
                'foreign_table_where' => 'AND tx_pxadealers_domain_model_dealer.pid=###CURRENT_PID### AND tx_pxadealers_domain_model_dealer.sys_language_uid IN (-1,0)',
                // @codingStandardsIgnoreEnd
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
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
                'size' => 16,
                'max' => 30,
                'eval' => 'datetime',
                'default' => 0
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 16,
                'max' => 30,
                'eval' => 'datetime',
                'default' => 0
            ],
        ],
        'name' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'country' => [
            'exclude' => 1,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.country',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'static_countries',
                'foreign_table_where' => 'ORDER BY static_countries.cn_short_en',
                'size' => 1,
                'maxitems' => 1,
                'multiple' => 0
            ],
        ],
        'address' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.address',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'zipcode' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.zipcode',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'lat' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.lat',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim'
            ],
        ],
        'lng' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.lng',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required,trim'
            ],
        ],
        'logo' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.logo',
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
                        ]
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
--palette--;;filePalette'
                            ],
                        ],
                    ],
                    'maxitems' => 1
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'show_street_view' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.show_street_view',
            'config' => [
                'type' => 'check',
                'default' => 1,
            ]
        ],
        'gm_position' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.gm_position',
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
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ]
        ],
        'phone' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.phone',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ]
        ],
        'website' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.website',
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
                            'blindLinkOptions' => 'mail'
                        ],

                        'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=1'
                    ]
                ],
                'softref' => 'typolink'
            ]
        ],
        'link' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.link',
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
                            'blindLinkOptions' => 'mail'
                        ],

                        'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=1'
                    ]
                ],
                'softref' => 'typolink'
            ]
        ],
        'email' => [
            'exclude' => 0,
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.email',
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
            'label' => $ll . 'tx_pxadealers_domain_model_dealers.description',
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
