<?php
declare(strict_types=1);

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
     * Google geocoding api url
     *
     * @var string $apiUrl
     */
    const API_GEOCODING_URL = 'https://maps.google.com/maps/api/geocode/json?key=%s&address=%s&language=%s';

    /**
     * Google api to suggest places
     */
    const PLACE_SUGGEST_URL = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=%s&types=geocode&language=%s&key=%s';

    /**
     * Google Api Key
     *
     * @var string
     */
    protected $apiKey = '';

    /**
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get suggest of search city
     *
     * @param string $searchTerm
     * @return array
     */
    public function getPlaceSuggest(string $searchTerm): array
    {
        $apiUrl = sprintf(
            static::PLACE_SUGGEST_URL,
            $searchTerm,
            $GLOBALS['TSFE']->config['language'] ?: 'en',
            $this->apiKey
        );

        return json_decode(GeneralUtility::getUrl($apiUrl), true);
    }

    /**
     * Get geocoding by address
     *
     * @param string $address
     * @param string $language
     * @return array
     */
    public function getGeocoding(string $address, string $language = null): array
    {
        $url = sprintf(
            static::API_GEOCODING_URL,
            $this->apiKey,
            urlencode($address),
            $language ?? $GLOBALS['TSFE']->config['config']['language']
        );

        $cacheManager = static::getCacheManager();
        $cacheHash = hash('sha1', $url);

        if ($cacheManager->has($cacheHash)) {
            $response = $cacheManager->get($cacheHash);
        } else {
            $responseJson = GeneralUtility::getURL($url, false);
            $response = json_decode($responseJson, true);

            $cacheManager->set($cacheHash, $response, []);
        }

        return $response;
    }

    /**
     * Wrapper for cache manager
     * @return FrontendInterface
     */
    protected function getCacheManager(): FrontendInterface
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('pxa_dealers');
    }
}
