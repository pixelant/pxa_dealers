<?php

namespace Pixelant\PxaDealers\Utility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Andriy Oprysko <andriy@pixelant.se>, Pixelant
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GoogleApiUtility
 * @package Pixelant\PxaDealers\Utility
 */
class GoogleApiUtility
{

    /**
     * google geocodin api url
     *
     * @var string $apiUrl
     */
    const API_GEOCODING_URL = 'https://maps.google.com/maps/api/geocode/json';

    /**
     * get geocoding by address
     *
     * @param string $address
     * @param string $key
     * @param string $language
     * @return array
     */
    public static function getGeocoding($address, $key, $language = null)
    {
        if ($language === null) {
            $language = $GLOBALS['TSFE']->config['config']['language'];
        }

        $url = self::API_GEOCODING_URL . '?key=' . $key . '&address=' . urlencode($address) . '&language=' . $language;

        //check cache
        $cacheKey = hash('sha1', $url);
        $cacheValue = self::getCache($cacheKey);
        if ($cacheValue !== false && !empty($cacheValue)) {
            $responseJson = $cacheValue;
        } else {
            $responseJson = GeneralUtility::getURL($url, false);
            self::saveInCache($cacheKey, $responseJson);
        }

        return json_decode($responseJson, true);
    }

    /**
     * cache api results
     *
     * @param string $cacheKey
     * @return mixed
     */
    private static function getCache($cacheKey)
    {
        if (self::getCacheManager()->has($cacheKey)) {
            return self::getCacheManager()->get($cacheKey);
        }

        return false;
    }

    /**
     * cache api results
     *
     * @param string $cacheKey
     * @param array $response google api answer
     * @return void
     */
    private static function saveInCache($cacheKey, $response)
    {
        self::getCacheManager()->set($cacheKey, $response, array());
    }

    /**
     * Wrapper for cache manager
     * @return FrontendInterface
     */
    private static function getCacheManager()
    {
        /** @var FrontendInterface $cache */
        static $cache = null;

        if ($cache === null) {
            $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('pxa_dealers');
        }

        return $cache;
    }
}
