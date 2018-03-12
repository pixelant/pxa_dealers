<?php
defined('TYPO3_MODE') || die('Access denied.');

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