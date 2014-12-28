<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_pxadealers_domain_model_dealers'] = array(
	'ctrl' => $TCA['tx_pxadealers_domain_model_dealers']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, telephone, website, adrress, zipcode, city, email, lat, lng, lat_lng_is_set',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, country, adrress, zipcode, city, telephone, email, website,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime'),
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
		'website' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.website',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'country' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:tx_pxadealers_domain_model_dealers.country',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
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
				'type' => 'check',
				'default' => 0
			),
		),
	),
);

?>