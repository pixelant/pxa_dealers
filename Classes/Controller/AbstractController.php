<?php

declare(strict_types=1);

namespace Pixelant\PxaDealers\Controller;

use Pixelant\PxaDealers\Domain\Repository\DealerRepository;
use Pixelant\PxaDealers\Utility\GoogleApiUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class AbstractController.
 */
abstract class AbstractController extends ActionController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     *  dealer repository.
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
    public function injectDealerRepository(DealerRepository $dealerRepository): void
    {
        $this->dealerRepository = $dealerRepository;
    }

    /**
     * get address cords.
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
            return [
                $response['results']['0']['geometry']['location']['lat'],
                $response['results']['0']['geometry']['location']['lng'],
            ];
        }

        $this->logger->error('Call to Google Geocoding API failed.', $response);

        return [null, null];
    }

    /**
     * Get instance of google api. It's not required always.
     * Initialize on demand.
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
