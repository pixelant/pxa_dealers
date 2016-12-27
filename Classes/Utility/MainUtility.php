<?php


namespace Pixelant\PxaDealers\Utility;


use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MainUtility
{

    /**
     * Generate FE link
     *
     * @param string $parameter Typolink parameter
     * @param bool $uriOnly
     * @return string
     *
     * @return string
     */
    public static function typoLink($parameter, $uriOnly = false)
    {
        $confLink = array(
            'parameter' => $parameter,
            'useCacheHash' => 1,
        );

        if (true === $uriOnly) {
            return self::getTSFE()->cObj->typoLink_URL($confLink);
        } else {
            return self::getTSFE()->cObj->typoLink('', $confLink);
        }
    }

    /**
     * @return TypoScriptFrontendController
     */
    public static function getTSFE()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return DatabaseConnection
     */
    public static function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Conver flexform array to normal array
     *
     * @param array $flexFormData
     * @return array
     */
    public static function flexForm2Array(array $flexFormData)
    {
        $settings = [];

        foreach ($rawSettings = $flexFormData['data'] as $key => $data) {
            $rawSettings = $flexFormData['data'][$key]['lDEF'];

            foreach ($rawSettings as $field => $rawSetting) {
                self::processFlexFormField($field, $rawSetting['vDEF'], $settings);
            }
        }

        return $settings;
    }

    /**
     * Process field
     *
     * @param string $field
     * @param array $value
     * @param array &$settings
     * @return void
     */
    private function processFlexFormField($field, $value, &$settings)
    {
        $fieldNameParts = GeneralUtility::trimExplode('.', $field);
        if (count($fieldNameParts) > 1) {
            $name = $fieldNameParts[0];

            unset($fieldNameParts[0]);

            if (!isset($settings[$name])) {
                $settings[$name] = [];
            }

            self::processFlexFormField(implode('.', $fieldNameParts), $value, $settings[$name]);
        } else {
            $settings[$fieldNameParts[0]] = $value;
        }
    }

    /**
     * @param string $label
     * @return string
     */
    public static function translate($label)
    {
        if ($label) {
            return self::getLang()->sL('LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:' . $label);
        }

        return '';
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    public static function getLang()
    {
        return $GLOBALS['LANG'];
    }
}