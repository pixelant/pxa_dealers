<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Utility;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class MainUtility.
 */
class MainUtility
{
    /**
     * Generate FE link.
     *
     * @param string $parameter Typolink parameter
     * @param bool $uriOnly
     * @return string
     *
     * @return string
     */
    public static function typoLink(string $parameter, bool $uriOnly = false)
    {
        $confLink = [
            'parameter' => $parameter,
            'useCacheHash' => 1,
        ];

        if ($uriOnly) {
            return self::getTSFE()->cObj->typoLink_URL($confLink);
        }

        return self::getTSFE()->cObj->typoLink('', $confLink);
    }

    /**
     * @return TypoScriptFrontendController
     */
    public static function getTSFE(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Translate BE.
     *
     * @param string $label
     * @return string|null
     */
    public static function translate(string $label): ?string
    {
        return self::getLang()->sL('LLL:EXT:pxa_dealers/Resources/Private/Language/locallang_db.xlf:' . $label);
    }

    /**
     * @return LanguageService
     */
    public static function getLang(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
