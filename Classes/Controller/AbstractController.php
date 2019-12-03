<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Controller;

use Pixelant\PxaDealers\Domain\Repository\DealerRepository;
use Pixelant\PxaDealers\Utility\GoogleApiUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class AbstractController
 * @package Pixelant\PxaDealers\Controller
 */
abstract class AbstractController extends ActionController
{
    /**
     *  dealer repository
     *
     * @var DealerRepository
     */
    protected $dealerRepository = null;

    /**
     * @var GoogleApiUtility
     */
    protected $googleApi = null;

    /**
     * @param DealerRepository $dealerRepository
     */
    public function injectDealerRepository(DealerRepository $dealerRepository)
    {
        $this->dealerRepository = $dealerRepository;
    }

    /**
     * get address cords
     *
     * @param string $address
     * @return array
     */
    protected function getAddressInfo($address)
    {
        $response = $this->getGoogleApi()->getGeocoding(
            $address
        );

        if ($response['status'] === 'OK') {
            return array(
                $response['results']['0']['geometry']['location']['lat'],
                $response['results']['0']['geometry']['location']['lng']
            );
        }

        return [null, null];
    }

    /**
     * Get instance of google api. It's not required always.
     * Initialize on demand
     *
     * @return GoogleApiUtility
     */
    protected function getGoogleApi(): GoogleApiUtility
    {
        if ($this->googleApi === null) {
            $this->googleApi = GeneralUtility::makeInstance(
                GoogleApiUtility::class,
                $this->settings['map']['googleServerApiKey'] ?? ''
            );
        }

        return $this->googleApi;
    }
}
