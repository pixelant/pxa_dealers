<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_pxadealers_domain_model_dealers'] = array(
	'ctrl' => $TCA['tx_pxadealers_domain_model_dealers']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, logo, telephone, fax, website, buy_it_now, zipcode, city, country, country_zone email, lat, lng, lat_lng_is_set, show_street_view',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, logo, country, country_zone, zipcode, city, telephone, fax, email, website, buy_it_now, show_street_view, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_pxadealers_domain_model_dealers',
				'foreign_table_where' => 'AND tx_pxadealers_domain_model_dealers.pid=###CURRENT_PID### AND tx_pxadealers_domain_model_dealers.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'telephone' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.telephone',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'fax' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.fax',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'website' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.website',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'buy_it_now' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.buy_it_now',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),		
		'country' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.country',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'static_countries',
				'foreign_table_where' => 'ORDER BY static_countries.cn_short_en',
				'size' => 1,
				'autoSizeMax' => 30,
				'maxitems' => 1,
				'multiple' => 0,
				'wizards' => array(
					'_POSITION' => 'top',
					'suggest' => array(
						'type' => 'suggest'
					),
				),
			),
		),
		'country_zone' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.country_zone',
			//'displayCond' => 'FIELD:country:IN:220,36,54,13,41,65,148,14,104,74,97,29,157,72,170,93',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'static_country_zones',
				'foreign_table_where' => 'AND static_country_zones.zn_country_uid = ###REC_FIELD_country### ORDER BY static_country_zones.uid',
				'items' => array(
					array('LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.country_zone.none', 0)
				),
				'disableNoMatchingValueElement' => 1,
				'size' => 1,
				'autoSizeMax' => 30,
				'maxitems' => 1,
				'multiple' => 0,
				'default' => 0,
				'wizards' => array(
					'_POSITION' => 'top',
					'suggest' => array(
						'type' => 'suggest'
					),
				),
			),
		),
		'adrress' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.adrress',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'zipcode' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.zipcode',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'required,trim'
			),
		),

		'zipcode_search' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.zipcode_search',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),

		'city' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.city',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'required,trim'
			),
		),

		'email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.email',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),

		'lat' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.lat',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'lng' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.lng',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'lat_lng_is_set' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.lat_lng_is_set',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'logo' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.logo',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'uploadfolder' => 'uploads/tx_pxadealers',
				'show_thumbs' => 1,
				'size' => 5,
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'disallowed' => '',
			),
		),
		'show_street_view' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.show_street_view',
				'config' => array(
						'type' => 'check',
						'default' => 1,
				),
		),
	),
);

?>