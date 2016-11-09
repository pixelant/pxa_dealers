<?php


namespace Pixelant\PxaDealers\Utility;


use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MainUtility {

    /**
     * Generate FE link
     *
     * @param string $parameter Typolink parameter
     * @param bool $uriOnly
     * @return string
     *
     * @return string
     */
    static public function typoLink($parameter, $uriOnly = FALSE) {
        $confLink = array(
            'parameter' => $parameter,
            'useCacheHash' => 1,
        );

        if(TRUE === $uriOnly) {
            return self::getTSFE()->cObj->typoLink_URL($confLink);
        } else {
            return self::getTSFE()->cObj->typoLink('', $confLink);
        }
    }

    /**
     * @return TypoScriptFrontendController
     */
    static public function getTSFE() {
        return $GLOBALS['TSFE'];
    }
}