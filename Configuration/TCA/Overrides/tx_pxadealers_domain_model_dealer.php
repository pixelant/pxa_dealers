<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    'pxa_dealers',
    'tx_pxadealers_domain_model_dealer'
);
