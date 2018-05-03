<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
            'pxa_dealers',
            'tx_pxadealers_domain_model_dealer'
            // Do not use the default field name ("categories"), which is already used
            // Also do not use a field name containing "categories" (see http://forum.typo3.org/index.php/t/199595/)
        );
    }
);

if (version_compare(TYPO3_version, '8.6', '>=')) {
    $columns = &$GLOBALS['TCA']['tx_pxadealers_domain_model_dealer']['columns'];

    unset($columns['website']['config']['wizards']);
    $columns['website']['config']['renderType'] = 'inputLink';
    $columns['website']['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'] = 'mail';

    unset($columns['link']['config']['wizards']);
    $columns['link']['config']['renderType'] = 'inputLink';
    $columns['link']['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'] = 'mail';

    unset($columns['email']['config']['wizards']);
    $columns['email']['config']['renderType'] = 'inputLink';
    $columns['email']['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'] = 'url,spec,file,folder,page';

    unset($columns['description']['config']['wizards']);
    $columns['description']['config']['enableRTE'] = true;
}
